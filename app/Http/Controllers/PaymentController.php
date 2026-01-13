<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function token(Request $request)
    {
        $request->validate([
            'address'     => 'required|string',
            'ongkir'      => 'required|numeric|min:1',
            'courier'     => 'required|string',
            'coupon_code' => 'nullable|string',
        ]);

        $user = auth()->user();
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart kosong'], 400);
        }

        // Hitung subtotal
        $subtotal = $cart->items->sum(fn($i) => $i->product->price * $i->qty);
        
        // ✅ HANDLE KUPON
        $discount = 0;
        $couponId = null;
        $couponCode = $request->coupon_code;

        if ($couponCode) {
            $coupon = \App\Models\Coupon::where('code', $couponCode)->first();

            if ($coupon && $coupon->isValid() && $coupon->canBeUsedBy($user->id)) {
                $discount = $coupon->calculateDiscount($subtotal);
                
                if ($discount > 0) {
                    $couponId = $coupon->id;
                    \Log::info('✅ Kupon diterapkan', [
                        'code' => $couponCode,
                        'discount' => $discount
                    ]);
                }
            } else {
                \Log::warning('⚠️ Kupon tidak valid', ['code' => $couponCode]);
            }
        }

        $ongkir = (int) $request->ongkir;
        $total  = (int) ($subtotal - $discount + $ongkir);

        if ($total <= 0) {
            return response()->json(['message' => 'Total tidak valid'], 400);
        }

        \Log::info('💰 Payment Calculation', [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'ongkir' => $ongkir,
            'total' => $total
        ]);

        // ✅ BUAT ORDER DENGAN DISKON
        $order = Order::create([
            'user_id'        => $user->id,
            'order_code'     => 'RG-' . time() . '-' . rand(1000, 9999),
            'address'        => $request->address,
            'courier'        => $request->courier,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'coupon_id'      => $couponId,
            'ongkir'         => $ongkir,
            'total'          => $total,
            'status'         => 'pending',
            'payment_method' => 'midtrans',
        ]);

        // Simpan order items
        foreach ($cart->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'qty'        => $item->qty,
                'price'      => $item->product->price,
            ]);
        }

        // ✅ INCREMENT USAGE KUPON SETELAH ORDER DIBUAT
        if ($couponId) {
            $coupon->incrementUsage($user->id);
        }

        // Clear cart
        $cart->items()->delete();

        // ✅ MIDTRANS PARAMS DENGAN ITEM DETAILS LENGKAP
        $itemDetails = [
            [
                'id'       => 'subtotal',
                'price'    => (int) $subtotal,
                'quantity' => 1,
                'name'     => 'Subtotal Produk'
            ],
            [
                'id'       => 'ongkir',
                'price'    => (int) $ongkir,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim (' . $request->courier . ')'
            ]
        ];

        // ✅ TAMBAHKAN DISKON SEBAGAI ITEM DENGAN HARGA NEGATIF
        if ($discount > 0) {
            $itemDetails[] = [
                'id'       => 'discount',
                'price'    => -1 * (int) $discount,
                'quantity' => 1,
                'name'     => 'Diskon Kupon (' . $couponCode . ')'
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_code,
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => $itemDetails,
        ];

        \Log::info('🎫 Midtrans Params', $params);

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $order->id,
                'order_code' => $order->order_code
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Midtrans Error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Gagal mendapatkan token pembayaran: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * ⭐ CALLBACK DARI MIDTRANS SERVER (POST dari Midtrans)
     */
    public function callback(Request $request)
    {
        \Log::info('🔔 Midtrans Callback Received', $request->all());

        try {
            $serverKey = config('midtrans.server_key');
            
            // ✅ Validasi signature
            $hashed = hash('sha512', 
                $request->order_id . 
                $request->status_code . 
                $request->gross_amount . 
                $serverKey
            );

            if ($hashed !== $request->signature_key) {
                \Log::error('❌ Invalid Signature', [
                    'expected' => $hashed,
                    'received' => $request->signature_key
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // ✅ Cari order
            $order = Order::where('order_code', $request->order_id)->first();

            if (!$order) {
                \Log::error('❌ Order Not Found', ['order_code' => $request->order_id]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // ✅ Mapping status
            $transactionStatus = $request->transaction_status;
            $fraudStatus = $request->fraud_status ?? 'accept';

            \Log::info('📝 Transaction Status', [
                'order_code' => $order->order_code,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $request->payment_type
            ]);

            // ✅ Update status berdasarkan transaction_status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $status = 'paid';
                } else {
                    $status = 'pending';
                }
            } elseif ($transactionStatus == 'settlement') {
                $status = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $status = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $status = 'canceled';
            } else {
                $status = 'pending';
            }

            // ✅ Update order
            $order->update([
                'status'                   => $status,
                'payment_status'           => $transactionStatus,
                'payment_method'           => $request->payment_type ?? 'midtrans',
                'payment_time'             => now(),
                'midtrans_transaction_id'  => $request->transaction_id ?? null,
                'midtrans_response'        => json_encode($request->all()),
            ]);

            \Log::info('✅ Order Updated Successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⭐ CHECK STATUS (Manual dari frontend)
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string'
        ]);

        try {
            $order = Order::where('order_code', $request->order_code)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // ✅ Cek status ke Midtrans
            $status = Transaction::status($order->order_code);

            \Log::info('🔍 Check Status from Midtrans', [
                'order_code' => $order->order_code,
                'transaction_status' => $status->transaction_status,
                'fraud_status' => $status->fraud_status ?? 'accept'
            ]);

            $transactionStatus = $status->transaction_status;
            $fraudStatus = $status->fraud_status ?? 'accept';

            // ✅ Mapping status
            if ($transactionStatus == 'capture') {
                $newStatus = ($fraudStatus == 'accept') ? 'paid' : 'pending';
            } elseif ($transactionStatus == 'settlement') {
                $newStatus = 'paid';
            } elseif ($transactionStatus == 'pending') {
                $newStatus = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $newStatus = 'canceled';
            } else {
                $newStatus = 'pending';
            }

            // ✅ Update jika status berubah
            if ($order->status !== $newStatus) {
                $order->update([
                    'status' => $newStatus,
                    'payment_status' => $transactionStatus,
                    'payment_time' => now(),
                    'midtrans_transaction_id' => $status->transaction_id ?? null,
                ]);

                \Log::info('✅ Status Updated via Check', [
                    'order_id' => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $newStatus
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Check Status Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}