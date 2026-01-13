<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product', 'items.review'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function pay(Order $order)
    {
        // Validasi authorization
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }

        // Validasi status
        if ($order->status !== 'pending') {
            return redirect()->route('orders')->with('error', 'Order ini sudah diproses atau tidak dapat dibayar.');
        }

        // Validasi waktu (order tidak boleh lebih dari 2 jam)
        if ($order->created_at->diffInHours(now()) > 2) {
            $order->update(['status' => 'expired']);
            return redirect()->route('orders')->with('error', 'Waktu pembayaran telah habis. Silakan buat pesanan baru.');
        }

        // Konfigurasi Midtrans
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
        
        if (empty(Config::$serverKey)) {
            \Log::error('Midtrans server key tidak ditemukan');
            return redirect()->route('orders')->with('error', 
                'Sistem pembayaran belum dikonfigurasi. Silakan hubungi admin.'
            );
        }

        // ⭐ GENERATE ORDER CODE BARU UNTUK RETRY (FIX ERROR "already taken")
        $baseOrderCode = preg_replace('/-R\d+$/', '', $order->order_code); // Remove old retry suffix
        $retryCount = \DB::table('orders')
            ->where('order_code', 'LIKE', $baseOrderCode . '%')
            ->count();
        
        if ($retryCount > 1 || strpos($order->order_code, '-R') !== false) {
            // Sudah pernah retry, buat order code baru
            $newOrderCode = $baseOrderCode . '-R' . time();
        } else {
            // First payment attempt, pakai order code original
            $newOrderCode = $order->order_code;
        }

        \Log::info('🎫 Generating Midtrans token for payment', [
            'order_id' => $order->id,
            'original_code' => $order->order_code,
            'new_code' => $newOrderCode,
            'total' => $order->total
        ]);

        // ⭐ BUILD ITEM DETAILS UNTUK MIDTRANS
        $itemDetails = [
            [
                'id'       => 'subtotal',
                'price'    => (int) $order->subtotal,
                'quantity' => 1,
                'name'     => 'Subtotal Produk'
            ],
            [
                'id'       => 'ongkir',
                'price'    => (int) $order->ongkir,
                'quantity' => 1,
                'name'     => 'Ongkos Kirim'
            ]
        ];

        // Tambahkan diskon jika ada
        if ($order->discount > 0) {
            $itemDetails[] = [
                'id'       => 'discount',
                'price'    => -1 * (int) $order->discount,
                'quantity' => 1,
                'name'     => 'Diskon Kupon'
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $newOrderCode,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email'      => auth()->user()->email,
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Update order code jika berubah
            if ($newOrderCode !== $order->order_code) {
                $order->update(['order_code' => $newOrderCode]);
                \Log::info('✅ Order code updated', [
                    'order_id' => $order->id,
                    'new_code' => $newOrderCode
                ]);
            }
            
            \Log::info('✅ Snap token generated successfully');
            
        } catch (\Exception $e) {
            \Log::error('❌ Midtrans Error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // ⭐ ERROR MESSAGE YANG LEBIH USER-FRIENDLY
            $errorMessage = 'Terjadi kesalahan sistem pembayaran.';
            
            if (strpos($e->getMessage(), 'already been taken') !== false) {
                $errorMessage = 'Nomor pesanan sudah digunakan. Silakan muat ulang halaman dan coba lagi.';
            } elseif (strpos($e->getMessage(), 'gross_amount') !== false) {
                $errorMessage = 'Total pembayaran tidak valid. Silakan hubungi admin.';
            } elseif (strpos($e->getMessage(), 'server_key') !== false) {
                $errorMessage = 'Konfigurasi pembayaran bermasalah. Silakan hubungi admin.';
            }
            
            return redirect()->route('orders')->with('error', $errorMessage);
        }

        return view('orders.pay', compact('order', 'snapToken'));
    }

    public function processPayment(Order $order, Request $request)
    {
        // Validasi authorization
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('orders')->with('error', 'Order ini sudah diproses.');
        }

        $order->update([
            'status' => 'paid',
            'payment_time' => now()
        ]);

        \Log::info('✅ Payment processed manually', [
            'order_id' => $order->id,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('orders')->with('success', 'Pembayaran berhasil! Pesanan Anda sedang diproses.');
    }

    /**
     * ⭐ CANCEL ORDER - METHOD YANG HILANG
     */
    public function cancel(Order $order)
    {
        // Validasi authorization
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Tidak dapat membatalkan pesanan orang lain');
        }

        // Validasi status - hanya pending yang bisa di-cancel
        if ($order->status !== 'pending') {
            return redirect()->route('orders')->with('error', 
                'Hanya pesanan dengan status menunggu pembayaran yang dapat dibatalkan.'
            );
        }

        try {
            // Update status ke canceled
            $order->update([
                'status' => 'canceled',
            ]);

            \Log::info('✅ Order canceled successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'user_id' => auth()->id(),
                'canceled_at' => now()
            ]);

            return redirect()->route('orders')->with('success', 'Pesanan berhasil dibatalkan.');

        } catch (\Exception $e) {
            \Log::error('❌ Cancel order failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('orders')->with('error', 
                'Gagal membatalkan pesanan. Silakan coba lagi atau hubungi admin.'
            );
        }
    }

    /**
     * ⭐ AUTO-EXPIRE OLD PENDING ORDERS (Optional - bisa dipanggil via scheduler)
     */
    public function expireOldOrders()
    {
        $expiredOrders = Order::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(2))
            ->get();

        foreach ($expiredOrders as $order) {
            $order->update(['status' => 'expired']);
            
            \Log::info('⏰ Order auto-expired', [
                'order_id' => $order->id,
                'order_code' => $order->order_code
            ]);
        }

        return response()->json([
            'success' => true,
            'expired_count' => $expiredOrders->count()
        ]);
    }
}