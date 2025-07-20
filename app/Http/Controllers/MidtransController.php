<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Transaction;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }
    // Notifikasi dari Midtrans
    public function notificationHandler(Request $request)
    {
        try {
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $midtrans_order_id = $notif->order_id;

            // Log incoming notification untuk debugging
            Log::info('Midtrans notification received', [
                'order_id' => $midtrans_order_id,
                'transaction_status' => $transaction,
                'raw_notification' => $request->all()
            ]);

            // Cari order berdasarkan midtrans_order_id (bukan order_id)
            $order = Order::where('midtrans_order_id', $midtrans_order_id)->first();

            if (!$order) {
                Log::warning('Order not found for notification', [
                    'midtrans_order_id' => $midtrans_order_id
                ]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Update status_order (bukan status)
            if ($transaction === 'settlement') {
                $previousStatus = $order->status_order;
                $order->status_order = 'lunas'; // Sesuai dengan enum yang digunakan

                // TIDAK kurangi stok di sini, hanya update status ke lunas
                // Stok akan dikurangi ketika admin ubah status ke "selesai"
                
                Log::info('Order status updated to lunas (payment complete)', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'previous_status' => $previousStatus,
                    'note' => 'Stock will be reduced when status changed to selesai'
                ]);
            } elseif ($transaction === 'expire' || $transaction === 'cancel') {
                $order->status_order = 'failed';
                Log::info('Order status updated to failed', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'transaction_status' => $transaction
                ]);
            } elseif ($transaction === 'pending') {
                // Status pending, tidak perlu update
                Log::info('Order remains pending', [
                    'midtrans_order_id' => $midtrans_order_id
                ]);
            }

            $order->save();

            // Log untuk debugging
            Log::info('Midtrans notification processed successfully', [
                'midtrans_order_id' => $midtrans_order_id,
                'transaction_status' => $transaction,
                'order_status' => $order->status_order
            ]);

            return response()->json(['message' => 'Notification handled successfully']);
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }

    // Validasi status pesanan sebelum lanjut bayar
    public function validateOrderStatusBeforePay($midtrans_order_id)
    {
        try {
            $status = Transaction::status($midtrans_order_id);
            $transaction_status = $status->transaction_status ?? null;

            if ($transaction_status !== 'pending') {
                return redirect('/keranjang')->with('error', 'Pesanan sudah tidak bisa dibayar. Silakan checkout ulang.');
            }
        } catch (\Exception $e) {
            return redirect('/keranjang')->with('error', 'Error validasi status: ' . $e->getMessage());
        }
    }

    // Fungsi bayar ulang
    public function payAgain($midtrans_order_id)
    {
        $order = Order::where('midtrans_order_id', $midtrans_order_id)->firstOrFail();

        try {
            $status = Transaction::status($midtrans_order_id);

            if (($status->transaction_status ?? null) !== 'pending') {
                return redirect()->route('home.keranjang')->with('error', 'Transaksi tidak bisa dibayar ulang.');
            }

            $user = User::find($order->user_id);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->midtrans_order_id,
                    'gross_amount' => $order->total_order,
                ],
                'customer_details' => [
                    'first_name' => $user->name ?? 'Customer',
                    'email' => $user->email ?? 'customer@example.com',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            return view('home.snap_checkout', compact('snapToken', 'order'));
        } catch (\Exception $e) {
            return redirect()->route('home.keranjang')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
