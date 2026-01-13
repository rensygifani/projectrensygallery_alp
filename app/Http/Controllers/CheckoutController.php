<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Coupon; // ⭐ TAMBAHKAN INI

class CheckoutController extends Controller
{
    public function form(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        $items = auth()->user()->cart->items()->with('product')->get();
        $total = $items->sum(fn($i) => $i->product->price * $i->qty);

        // Ambil data provinsi
        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key'),
            'Accept' => 'application/json'
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        $provinces = $response->successful() ? $response->json()['data'] : [];

        return view('checkout.form', compact('items', 'total', 'provinces'));
    }

    public function buyNow(Request $request, Product $product)
    {
        $user = auth()->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->qty += 1;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'qty' => 1
            ]);
        }

        $items = CartItem::with('product')->where('cart_id', $cart->id)->get();
        $total = $items->sum(fn($i) => $i->product->price * $i->qty);

        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key'),
            'Accept' => 'application/json'
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        $provinces = $response->successful() ? $response->json()['data'] : [];

        return view('checkout.form', compact('items', 'total', 'provinces'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'payment_method' => 'required',
        ]);

        // MODE BUY NOW
        if ($request->mode === 'buy_now') {
            $product = Product::findOrFail($request->product_id);

            $order = Order::create([
                'user_id' => auth()->id(),
                'address' => $request->address,
                'payment_method' => $request->payment_method,
                'total' => $product->price
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'qty' => 1,
                'price' => $product->price
            ]);

            return redirect()->route('orders')
                ->with('success', 'Pembelian berhasil (Buy Now)');
        }

        // MODE CART
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        $items = $cart->items()->whereIn('id', $request->items)->with('product')->get();
        $subtotal = $items->sum(fn($i) => $i->product->price * $i->qty);

        // ⭐ HANDLE KUPON
        $discount = 0;
        $couponId = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();

            if ($coupon && $coupon->canBeUsedBy(auth()->id())) {
                $discount = $coupon->calculateDiscount($subtotal);
                
                if ($discount > 0) {
                    $coupon->incrementUsage(auth()->id());
                    $couponId = $coupon->id;
                }
            }
        }

        $total = $subtotal - $discount;

        // Buat order dengan kupon
        $order = Order::create([
            'user_id' => auth()->id(),
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon_id' => $couponId
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->product->price
            ]);
        }

        $cart->items()->whereIn('id', $request->items)->delete();

        return redirect()->route('orders')
            ->with('success', 'Checkout berhasil');
    }
}