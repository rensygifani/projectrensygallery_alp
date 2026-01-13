@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <h2 class="fw-bold pastel-section-title">Product Detail</h2>

            <div class="card pastel-show-card shadow-sm p-4 mt-3">
                <div class="row">
                    <div class="col-md-5">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="pastel-thumb-large">
                        @else
                            <div class="pastel-thumb-large d-flex align-items-center justify-content-center">
                                {{ $product->id }}
                            </div>
                        @endif
                    </div>

                    <div class="col-md-7">
                        <h4 class="fw-bold">{{ $product->name }}</h4>

                        <span class="badge pastel-badge mb-2">
                            {{ $product->category->name ?? 'No Category' }}
                        </span>

                        <x-share-buttons :product="$product" />

                        
                        <p class="text-muted">
                            {{ $product->description }}
                        </p>

                        <h3 class="fw-bold text-danger">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </h3>

                        {{-- 🔹 ADD TO CART --}}
                        <form method="POST" action="{{ route('cart.add', $product->id) }}" class="mt-3">
                            @csrf
                            <button class="btn pastel-btn w-100">
                                + Add to Cart
                            </button>
                        </form>

                        <form method="POST" action="{{ route('buy.now', $product->id) }}" class="mt-2">
                            @csrf
                            <button class="btn pastel-danger w-100">
                                ⚡ Beli Sekarang
                            </button>
                        </form>


                        @auth
                            <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}" class="mt-1">
                                @csrf
                                <button class="btn btn-sm pastel-outline w-100">
                                    ❤️ Wishlist
                                </button>
                            </form>
                        @endauth

                        @php
                            $rating = round($product->averageRating() ?? 0);
                        @endphp

                        <div class="mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $rating)
                                    <span class="text-warning fs-5">★</span>
                                @else
                                    <span class="text-muted fs-5">☆</span>
                                @endif
                            @endfor

                            <span class="small text-muted ms-1">
                                ({{ number_format($product->averageRating() ?? 0, 1) }})
                            </span>

                        </div>

                         {{-- 💬 REVIEW LIST --}}
                    <hr>

                    <h5 class="fw-bold mt-3">💬 Review Pembeli</h5>

                    @if ($product->reviews->isEmpty())
                        <div class="text-muted small">
                            Belum ada review
                        </div>
                    @else
                        @foreach ($product->reviews as $review)
                            <div class="card p-3 mb-2 pastel-form-card">
                                <div class="fw-semibold">
                                    {{ $review->user->name ?? 'User tidak ditemukan' }}
                                    <span class="ms-2">
                                        {{ str_repeat('⭐', $review->rating) }}
                                    </span>
                                </div>

                                <div class="small text-muted">
                                    {{ $review->created_at->diffForHumans() }}
                                </div>

                                @if ($review->comment)
                                    <div class="mt-1">
                                        {{ $review->comment }}
                                    </div>
                                @else
                                    <div class="fst-italic text-muted small">
                                        (Tidak ada komentar)
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif

                        {{-- ⭐ {{ $product->averageRating() ?? 0 }} / 5

                        @foreach ($product->reviews as $review)
                            <div class="card p-3 mb-2">
                                <div class="fw-semibold">
                                    {{ $review->user->name }}
                                    {{ str_repeat('⭐', $review->rating) }}
                                </div>
                                <div class="small text-muted">
                                    {{ $review->created_at->diffForHumans() }}
                                </div>
                                {{ $review->comment }}
                            </div>
                        @endforeach --}}



                        <div class="mt-4 d-flex gap-2">
                            <a href="{{ route('products') }}" class="btn pastel-outline px-4">
                                Back
                            </a>

                            @auth
                                <a href="{{ route('products.edit', $product->id) }}" class="btn pastel-warning px-4">
                                    Edit
                                </a>

                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this product?');"
                                    style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger px-4">
                                        Delete
                                    </button>
                                </form>
                            @endauth
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection












{{-- @extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8">
        <h2 class="fw-bold pastel-section-title">Product Detail</h2>

        <div class="card pastel-show-card shadow-sm p-4 mt-3">
            <div class="row">
                <div class="col-md-5">
                    @if ($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="pastel-thumb-large">
                    @else
                        <div class="pastel-thumb-large d-flex align-items-center justify-content-center">
                            {{ $product->id }}
                        </div>
                    @endif
                </div>

                <div class="col-md-7">
                    <h4 class="fw-bold">{{ $product->name }}</h4>
                    <span class="badge pastel-badge mb-2">{{ $product->category->name ?? 'No Category' }}</span>
                    <p class="text-muted">{{ $product->description }}</p>

                    <h3 class="fw-bold text-danger">Rp {{ number_format($product->price,0,',','.') }}</h3>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('products') }}" class="btn pastel-outline px-4">Back</a>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn pastel-warning px-4">Edit</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection --}}
