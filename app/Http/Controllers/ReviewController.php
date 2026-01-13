<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
{
    $request->validate([
        'order_item_id' => 'required|exists:order_items,id',
        'rating'        => 'required|integer|min:1|max:5',
        'comment'       => 'nullable|string|max:500'
    ]);

    Review::create([
        'user_id'       => auth()->id(),
        'product_id'    => $product->id,
        'order_item_id' => $request->order_item_id,
        'rating'        => $request->rating,
        'comment'       => $request->comment
    ]);

    return back()->with('success', 'Review berhasil dikirim');
}

}

