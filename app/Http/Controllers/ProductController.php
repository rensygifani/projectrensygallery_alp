<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    function index(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%$q%")
                    ->orWhere('description', 'LIKE', "%$q%");
            });
        }

        // Price Filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->min_price ?? 0;
            $max = $request->max_price ?? PHP_INT_MAX;
            $query->whereBetween('price', [$min, $max]);
        }

        // CATEGORY FILTER
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Sort
        if ($request->filled('sort')) {
            $sort = $request->sort;

            if ($sort === 'name_asc') $query->orderBy('name', 'asc');
            if ($sort === 'name_desc') $query->orderBy('name', 'desc');
            if ($sort === 'price_asc') $query->orderBy('price', 'asc');
            if ($sort === 'price_desc') $query->orderBy('price', 'desc');

        } else {
            // $query->latest();
            $query->orderBy('id', 'desc');

        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('products.list', compact('products', 'categories'));
    }

    function create()
    {
        $categories = Category::all();
        return view('products.form', compact('categories'));
    }

    function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }


        Product::create($data);

        return redirect()->route('products')->with('success', 'Product created!');
    }

    function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('products.form', compact('product', 'categories'));
    }

    function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'description' => 'nullable',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

         if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }


        $product->update($data);

        // return redirect()->route('products.show', $product->id)->with('success', 'Product updated!');
        return redirect()->route('products')->with('success', 'Product updated!');

    }

    function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Hapus file gambar jika ada
        if ($product->image && \Storage::disk('public')->exists($product->image)) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products')->with('success', 'Product deleted!');
    }

}
