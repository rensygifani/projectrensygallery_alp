@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8">
        <h2 class="fw-bold pastel-section-title">
            {{ isset($product) ? 'Edit Product' : 'Create Product' }}
        </h2>

        <div class="card pastel-form-card shadow-sm p-4 mt-3">

            <form
                action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @if(isset($product))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="fw-semibold">Category</label>
                    <select name="category_id" class="form-control pastel-input">
                        <option value="">-- Choose Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ isset($product) && $product->category_id==$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Name</label>
                    <input type="text" name="name" class="form-control pastel-input" value="{{ $product->name ?? old('name') }}">
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Description</label>
                    <textarea name="description" rows="4" class="form-control pastel-input">{{ $product->description ?? old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Price</label>
                    <input type="number" name="price" class="form-control pastel-input" value="{{ $product->price ?? old('price') }}">
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Image (optional)</label>
                    <input type="file" name="image" class="form-control pastel-input">
                    @if(isset($product) && $product->image)
                        <small class="text-muted">Current: {{ $product->image }}</small>
                    @endif
                </div>

                <button class="btn pastel-btn px-4">
                    {{ isset($product) ? 'Update' : 'Submit' }}
                </button>

            </form>

        </div>
    </div>

    {{-- right column (preview/info)
    <div class="col-md-4">
        <div class="card shadow-sm pastel-info-card p-3 mt-3">
            <h6 class="fw-bold">Tips</h6>
            <p class="text-muted small">Gunakan gambar background sederhana & ukuran minimal 800×800 untuk hasil terbaik.</p>
        </div>
    </div> --}}
</div>

@endsection
