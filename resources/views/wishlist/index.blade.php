@extends('layouts.app')

@section('content')

<h3 class="fw-bold mb-3">❤️ My Wishlist</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-4">
    ← Back to Products
</a>

@if($wishlists->isEmpty())
    <div class="card p-4 text-center pastel-form-card">
        <div class="fs-1 mb-2">💔</div>
        <div class="fw-semibold">Wishlist masih kosong</div>
        <div class="text-muted small">
            Simpan produk favoritmu di sini ✨
        </div>
    </div>
@else

<div class="row g-3">
@foreach($wishlists as $item)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card pastel-form-card shadow-sm h-100 p-3">

            {{-- IMAGE --}}
            <div class="text-center mb-2">
                <img src="{{ asset('storage/'.$item->product->image) }}"
                     class="rounded"
                     style="width:100%; max-height:160px; object-fit:cover;">
            </div>

            {{-- INFO --}}
            <div class="text-center">
                <div class="fw-bold">
                    {{ $item->product->name }}
                </div>

                <div class="fw-bold text-danger my-1">
                    Rp {{ number_format($item->product->price,0,',','.') }}
                </div>

                <div class="small text-muted mb-2">
                    {{ $item->product->category->name ?? 'No Category' }}
                </div>
            </div>

            {{-- ACTION --}}
            <div class="d-grid gap-2 mt-auto">

                {{-- ADD TO CART --}}
                <form method="POST" action="{{ route('cart.add', $item->product->id) }}">
                    @csrf
                    <button class="btn pastel-btn w-100">
                        🛒 Add to Cart
                    </button>
                </form>

                {{-- REMOVE WISHLIST --}}
                <form method="POST" action="{{ route('wishlist.remove', $item->id) }}">
                    @csrf
                    <button class="btn pastel-danger btn-sm w-100">
                        🗑 Remove Wishlist
                    </button>
                </form>

            </div>

        </div>
    </div>
@endforeach
</div>

@endif

@endsection
