<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    // Katalog produk
    public function katalog(Request $request)
    {
        $kategori = $request->kategori;
        $categories = Category::orderBy('nama_kategori')->get();

        // Query produk yang bisa dilihat customer:
        // 1. Produk toko (supplier_id = null) - tidak perlu approval
        // 2. Produk supplier yang sudah di-approve (is_approved = 1)
        $products = Product::with(['images' => function ($query) {
            $query->orderBy('urutan', 'asc'); // Urutkan gambar berdasarkan urutan
        }, 'category'])
            ->where(function ($query) {
                $query->whereNull('supplier_id') // Produk toko
                    ->orWhere('is_approved', 1); // Atau produk supplier yang sudah approved
            })
            ->when($kategori, fn($q) => $q->where('category_id', $kategori))
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('home.katalog', compact('products', 'categories', 'kategori'));
    }

    // Keranjang
    public function keranjang()
    {
        $keranjang = session()->get('keranjang', []);
        $role = Auth::check() ? Auth::user()->role : 'customer';
        return view('home.keranjang', compact('keranjang', 'role'));
    }

    // Tambah item ke keranjang
    public function tambahKeranjang(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $keranjang = session()->get('keranjang', []);
        $keranjang[$id] = [
            'id' => $product->id,
            'nama' => $product->nama_produk,
            'harga' => $product->harga_jual,
            'qty' => ($keranjang[$id]['qty'] ?? 0) + 1,
        ];
        session()->put('keranjang', $keranjang);
        return redirect()->route('home.keranjang')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    // Update qty item di keranjang
    public function updateKeranjang(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1|max:999'
        ]);
        $keranjang = session()->get('keranjang', []);
        if (isset($keranjang[$id])) {
            $keranjang[$id]['qty'] = $request->qty;
            session()->put('keranjang', $keranjang);
        }
        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    // Hapus item dari keranjang
    public function hapusKeranjang($id)
    {
        $keranjang = session()->get('keranjang', []);
        unset($keranjang[$id]);
        session()->put('keranjang', $keranjang);
        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    // Checkout dengan validasi domisili Tegal & mitra min 10
    public function checkout(Request $request)
    {
        $keranjang = session()->get('keranjang', []);
        $user = Auth::user();
        $role = $user ? $user->role : 'customer';
        $totalQty = array_sum(array_column($keranjang, 'qty'));

        // 1. Keranjang tidak boleh kosong
        if (empty($keranjang)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        // 2. Jika mitra, validasi minimal pembelian 10 pcs
        if ($role == 'mitra' && $totalQty < 10) {
            return back()->with('error', 'Minimal pembelian untuk mitra adalah 10 pcs!');
        }

        // 3. Validasi alamat
        $alamat = trim($request->input('alamat', ''));
        if ($alamat !== '' && stripos($alamat, 'tegal') === false) {
            return back()->with('error', 'Jika Anda mengisi alamat, wajib mencantumkan kata "Tegal"!');
        }

        // 4. Hitung total
        $total = 0;
        foreach ($keranjang as $item) {
            $total += $item['qty'] * $item['harga'];
        }

        // 5. Buat midtrans_order_id unik
        $orderPrefix = strtoupper(Str::random(5)) . '-' . time();
        $midtransOrderId = 'INV-' . $orderPrefix;

        // 6. Insert ke orders
        $order = Order::create([
            'user_id' => Auth::id(),
            'tipe_order' => $role == 'mitra' ? 'mitra' : 'customer',
            'tanggal_order' => now(),
            'total_order' => $total,
            'status_order' => 'pending',
            'alamat_kirim' => $alamat,
            'midtrans_order_id' => $midtransOrderId,
        ]);

        // 7. Insert ke order_items
        foreach ($keranjang as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'qty' => $item['qty'],
                'harga_jual' => $item['harga'],
                'subtotal' => $item['qty'] * $item['harga'],
            ]);
        }

        // 8. MIDTRANS (jika perlu)
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            // Siapkan item details untuk Midtrans
            $itemDetails = [];
            foreach ($keranjang as $item) {
                $itemDetails[] = [
                    'id' => $item['id'],
                    'price' => $item['harga'],
                    'quantity' => $item['qty'],
                    'name' => $item['nama'], // Sesuaikan dengan key di session keranjang
                ];
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $midtransOrderId,
                    'gross_amount' => $total,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'enabled_payments' => ['bank_transfer', 'gopay', 'qris'],
            ];

            $snapToken = Snap::getSnapToken($payload);

            Payment::create([
                'order_id' => $order->id,
                'midtrans_order_id' => $midtransOrderId,
                'jumlah_bayar' => $total,
                'metode_bayar' => 'midtrans',
                'status_bayar' => 'pending',
            ]);

            // Bersihkan keranjang
            session()->forget('keranjang');

            return view('home.snap_checkout', [
                'snapToken' => $snapToken,
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('[Checkout Error] ' . $e->getMessage());
            return redirect()->route('home.keranjang')->with('error', 'Gagal membuat Snap Token: ' . $e->getMessage());
        }
    }

    // Halaman checkout sukses
    public function checkoutSuccess()
    {
        return view('home.checkout_success');
    }

    // Pesanan saya (list)
    public function pesananSaya()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        // Log untuk debugging - cek status orders  
        Log::info('Orders list loaded', [
            'user_id' => Auth::id(),
            'orders_count' => $orders->count(),
            'orders_status' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status_order,
                    'midtrans_id' => $order->midtrans_order_id,
                    'updated_at' => $order->updated_at
                ];
            })->toArray()
        ]);

        return view('home.orders', compact('orders'));
    }

    // Detail pesanan
    public function pesananDetail($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Log untuk debugging - pastikan data fresh dari database
        Log::info('Order detail loaded from database', [
            'order_id' => $order->id,
            'status_order' => $order->status_order,
            'midtrans_order_id' => $order->midtrans_order_id,
            'updated_at' => $order->updated_at
        ]);

        return view('home.order_detail', compact('order'));
    }

    // Lanjutkan pembayaran dengan order ID yang sama
    public function lanjutkanPembayaran($id)
    {
        // Ambil order yang statusnya pending milik user
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status_order', 'pending')
            ->firstOrFail();

        $user = Auth::user();

        // Setup Midtrans config
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            // STEP 1: Cek status transaksi di Midtrans terlebih dahulu
            Log::info('[Lanjutkan Pembayaran] Checking Midtrans transaction status', [
                'order_id' => $order->id,
                'midtrans_order_id' => $order->midtrans_order_id
            ]);

            try {
                $midtransStatus = Transaction::status($order->midtrans_order_id);
                $transactionStatus = $midtransStatus->transaction_status ?? 'unknown';

                Log::info('[Lanjutkan Pembayaran] Midtrans status check result', [
                    'transaction_status' => $transactionStatus,
                    'status_code' => $midtransStatus->status_code ?? 'unknown'
                ]);

                // Jika transaksi sudah settlement/success, redirect ke detail
                if (in_array($transactionStatus, ['settlement', 'capture', 'success'])) {
                    Log::info('[Lanjutkan Pembayaran] Transaction already completed', [
                        'transaction_status' => $transactionStatus
                    ]);
                    return redirect()->route('home.myorders.detail', $id)
                        ->with('info', 'Pembayaran sudah berhasil diproses.');
                }

                // Jika transaksi expire/cancel/failed, tidak bisa dilanjutkan
                if (in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure'])) {
                    Log::warning('[Lanjutkan Pembayaran] Transaction cannot be continued', [
                        'transaction_status' => $transactionStatus
                    ]);
                    return redirect()->route('home.myorders.detail', $id)
                        ->with('error', 'Transaksi sudah tidak valid. Status: ' . $transactionStatus);
                }

                // Jika pending, lanjutkan dengan token yang sudah ada jika memungkinkan
                if ($transactionStatus === 'pending') {
                    Log::info('[Lanjutkan Pembayaran] Transaction is pending, will try to reuse or create new token');
                }
            } catch (\Exception $statusError) {
                // Jika gagal cek status (mungkin order_id belum ada di Midtrans), lanjutkan proses normal
                Log::warning('[Lanjutkan Pembayaran] Cannot check Midtrans status, proceeding normally', [
                    'error' => $statusError->getMessage()
                ]);
            }

            // STEP 2: Coba gunakan snap token dari session terlebih dahulu
            $sessionSnapToken = session('snap_token_' . $order->id);
            if ($sessionSnapToken) {
                Log::info('[Lanjutkan Pembayaran] Using existing snap token from session');
                return view('home.snap_checkout', [
                    'snapToken' => $sessionSnapToken,
                    'order' => $order,
                    'isRetry' => true
                ]);
            }

            // STEP 3: Buat Snap token baru dengan order_id yang sudah ada
            // Siapkan item details dari order items
            $itemDetails = [];
            foreach ($order->items as $orderItem) {
                $itemDetails[] = [
                    'id' => $orderItem->product_id,
                    'price' => $orderItem->harga_jual,
                    'quantity' => $orderItem->qty,
                    'name' => $orderItem->product->nama_produk ?? 'Product #' . $orderItem->product_id,
                ];
            }

            Log::info('[Lanjutkan Pembayaran] Creating new Snap token', [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => $order->total_order
            ]);

            // Gunakan midtrans_order_id yang sudah ada (TIDAK MEMBUAT BARU)
            $payload = [
                'transaction_details' => [
                    'order_id' => $order->midtrans_order_id, // Pakai yang sudah ada
                    'gross_amount' => $order->total_order,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            // Generate snap token baru untuk order yang sama
            $snapToken = Snap::getSnapToken($payload);

            Log::info('[Lanjutkan Pembayaran] Snap token created successfully', [
                'token_length' => strlen($snapToken)
            ]);

            // Simpan snap token ke session untuk recovery jika diperlukan
            session(['snap_token_' . $order->id => $snapToken]);

            return view('home.snap_checkout', [
                'snapToken' => $snapToken,
                'order' => $order,
                'isRetry' => true // Flag untuk indikasi ini adalah retry payment
            ]);
        } catch (\Exception $e) {
            Log::error('[Lanjutkan Pembayaran Error] Order ID: ' . $order->id . ' - ' . $e->getMessage(), [
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode()
                ],
                'order_details' => [
                    'id' => $order->id,
                    'midtrans_order_id' => $order->midtrans_order_id,
                    'status' => $order->status_order
                ]
            ]);

            // Jika error "order_id has already been taken", coba buat order_id baru
            if (strpos($e->getMessage(), 'has already been taken') !== false) {
                Log::info('[Lanjutkan Pembayaran] Trying with new order ID due to duplicate error');

                try {
                    // Buat order_id baru dengan suffix retry
                    $newOrderId = $order->midtrans_order_id . '-R' . time();

                    Log::info('[Lanjutkan Pembayaran] Creating with new order ID', [
                        'original_order_id' => $order->midtrans_order_id,
                        'new_order_id' => $newOrderId
                    ]);

                    $newPayload = [
                        'transaction_details' => [
                            'order_id' => $newOrderId,
                            'gross_amount' => $order->total_order,
                        ],
                        'item_details' => $itemDetails,
                        'customer_details' => [
                            'first_name' => $user->name,
                            'email' => $user->email,
                        ],
                    ];

                    $snapToken = Snap::getSnapToken($newPayload);

                    // Update midtrans_order_id dengan yang baru
                    $order->midtrans_order_id = $newOrderId;
                    $order->save();

                    Log::info('[Lanjutkan Pembayaran] Success with new order ID', [
                        'new_order_id' => $newOrderId
                    ]);

                    // Simpan snap token ke session
                    session(['snap_token_' . $order->id => $snapToken]);

                    return view('home.snap_checkout', [
                        'snapToken' => $snapToken,
                        'order' => $order,
                        'isRetry' => true
                    ]);
                } catch (\Exception $retryError) {
                    Log::error('[Lanjutkan Pembayaran Retry Error]', [
                        'error' => $retryError->getMessage()
                    ]);

                    return redirect()->route('home.myorders.detail', $id)
                        ->with('error', 'Gagal melanjutkan pembayaran: ' . $retryError->getMessage());
                }
            }

            return redirect()->route('home.myorders.detail', $id)
                ->with('error', 'Gagal melanjutkan pembayaran: ' . $e->getMessage());
        }
    }
}
