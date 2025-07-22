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
            // Log raw request untuk debugging
            Log::info('Raw Midtrans notification received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw_input' => file_get_contents('php://input')
            ]);

            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $midtrans_order_id = $notif->order_id;
            $signature_key = $notif->signature_key ?? 'not_provided';

            // Log notification details
            Log::info('Midtrans notification parsed', [
                'order_id' => $midtrans_order_id,
                'transaction_status' => $transaction,
                'signature_key' => substr($signature_key, 0, 20) . '...',
                'settlement_time' => $notif->settlement_time ?? null,
                'payment_type' => $notif->payment_type ?? null
            ]);

            // Cari order berdasarkan midtrans_order_id (bukan order_id)
            $order = Order::where('midtrans_order_id', $midtrans_order_id)->first();

            if (!$order) {
                Log::warning('Order not found for notification', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'all_orders' => Order::pluck('midtrans_order_id')->toArray()
                ]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            Log::info('Order found', [
                'order_id' => $order->id,
                'current_status' => $order->status_order,
                'midtrans_order_id' => $order->midtrans_order_id
            ]);

            $previousStatus = $order->status_order;

            // Update status_order (bukan status)
            if ($transaction === 'settlement') {
                Log::info('Processing settlement transaction', [
                    'order_id' => $order->id,
                    'current_status' => $order->status_order,
                    'will_change_to' => 'lunas'
                ]);

                $order->status_order = 'lunas'; // Sesuai dengan enum yang digunakan

                // Verifikasi sebelum save
                Log::info('Before saving order', [
                    'order_id' => $order->id,
                    'status_in_memory' => $order->status_order,
                    'isDirty' => $order->isDirty(),
                    'dirtyAttributes' => $order->getDirty()
                ]);

                $saveResult = $order->save();

                Log::info('Order status updated to lunas (payment complete)', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'previous_status' => $previousStatus,
                    'new_status' => $order->status_order,
                    'save_result' => $saveResult,
                    'order_saved' => $order->wasChanged(),
                    'note' => 'Stock will be reduced when status changed to selesai'
                ]);

                // Double check dengan query langsung
                $checkOrder = Order::where('midtrans_order_id', $midtrans_order_id)->first();
                Log::info('Database verification after save', [
                    'db_status' => $checkOrder->status_order,
                    'updated_at' => $checkOrder->updated_at
                ]);
            } elseif ($transaction === 'expire' || $transaction === 'cancel') {
                $order->status_order = 'failed';
                $saveResult = $order->save();

                Log::info('Order status updated to failed', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'transaction_status' => $transaction,
                    'previous_status' => $previousStatus,
                    'new_status' => $order->status_order,
                    'save_result' => $saveResult
                ]);
            } elseif ($transaction === 'pending') {
                // Status pending, tidak perlu update
                Log::info('Order remains pending', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'current_status' => $order->status_order
                ]);
            } else {
                Log::warning('Unknown transaction status', [
                    'midtrans_order_id' => $midtrans_order_id,
                    'transaction_status' => $transaction,
                    'current_status' => $order->status_order
                ]);
            }

            // Refresh model untuk memastikan perubahan tersimpan
            $order->refresh();

            // Log untuk debugging
            Log::info('Midtrans notification processed successfully', [
                'midtrans_order_id' => $midtrans_order_id,
                'transaction_status' => $transaction,
                'order_status' => $order->status_order,
                'final_order_check' => [
                    'id' => $order->id,
                    'status' => $order->status_order,
                    'updated_at' => $order->updated_at
                ]
            ]);

            return response()->json([
                'message' => 'Notification handled successfully',
                'order_id' => $midtrans_order_id,
                'status' => $order->status_order
            ]);
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
