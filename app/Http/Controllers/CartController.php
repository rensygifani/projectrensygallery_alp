<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;


class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $items = $cart->items()->with('product')->get();
        $total = $items->sum(fn($i) => $i->product->price * $i->qty);

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Product $product)
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        $item = $cart->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->increment('qty');
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'qty' => 1
            ]);
        }

        return back()->with('success', 'Added to cart');
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);

        if ($request->action === 'plus') {
            $item->increment('qty');
        }

        if ($request->action === 'minus' && $item->qty > 1) {
            $item->decrement('qty');
        }

        return back();
        // CartItem::findOrFail($id)->update(['qty' => $request->qty]);
        // return back();
    }

    public function remove($id)
    {
        CartItem::findOrFail($id)->delete();
        return back();
    }
}
