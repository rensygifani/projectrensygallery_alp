@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8">
        <h2 class="fw-bold pastel-section-title">Product Detail</h2>

        <div class="card pastel-show-card shadow-sm p-4 mt-3">
            <div class="row">
                <div class="col-md-5">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}"
                             alt="{{ $product->name }}"
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

                    <p class="text-muted">
                        {{ $product->description }}
                    </p>

                    <h3 class="fw-bold text-danger">
                        Rp {{ number_format($product->price,0,',','.') }}
                    </h3>

                    {{-- 🔹 ADD TO CART --}}
                    <form method="POST" action="{{ route('cart.add', $product->id) }}" class="mt-3">
                        @csrf
                        <button class="btn pastel-btn w-100">
                            + Add to Cart
                        </button>
                    </form>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('products') }}"
                           class="btn pastel-outline px-4">
                            Back
                        </a>

                        @auth
                            <a href="{{ route('products.edit', $product->id) }}"
                               class="btn pastel-warning px-4">
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
                    @if($product->image)
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
