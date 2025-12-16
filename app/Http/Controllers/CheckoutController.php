<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function form(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();

        $items = $cart->items()
            ->whereIn('id', $request->items)
            ->with('product')
            ->get();

        $total = $items->sum(fn ($i) => $i->product->price * $i->qty);

        return view('checkout.form', compact('items', 'total'));
    }

    /**
     * Proses checkout
     */
    public function process(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'payment_method' => 'required',
            'items' => 'required|array|min:1'
        ]);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();

        // Ambil item TERPILIH SAJA
        $items = $cart->items()
            ->whereIn('id', $request->items)
            ->with('product')
            ->get();

        $total = $items->sum(fn ($i) => $i->product->price * $i->qty);

        // Buat order
        $order = Order::create([
            'user_id' => auth()->id(),
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'total' => $total
        ]);

        // Simpan item order
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->product->price
            ]);
        }

        // Hapus hanya item yang dibeli
        $cart->items()->whereIn('id', $request->items)->delete();

        return redirect()
            ->route('orders')
            ->with('success', 'Checkout berhasil');
    }
}
