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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    // Katalog produk
    public function katalog(Request $request)
    {
        $kategori = $request->kategori;
        $categories = Category::orderBy('nama_kategori')->get();

        $products = Product::with('images')
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
        return view('home.orders', compact('orders'));
    }

    // Detail pesanan
    public function pesananDetail($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
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

            // Simpan snap token ke session untuk recovery jika diperlukan
            session(['snap_token_' . $order->id => $snapToken]);

            return view('home.snap_checkout', [
                'snapToken' => $snapToken,
                'order' => $order,
                'isRetry' => true // Flag untuk indikasi ini adalah retry payment
            ]);
        } catch (\Exception $e) {
            Log::error('[Lanjutkan Pembayaran Error] Order ID: ' . $order->id . ' - ' . $e->getMessage());
            return redirect()->route('home.myorders.detail', $id)
                ->with('error', 'Gagal melanjutkan pembayaran: ' . $e->getMessage());
        }
    }
}
