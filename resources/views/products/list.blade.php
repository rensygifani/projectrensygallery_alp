@extends('layouts.app')

@section('content')
    {{-- HERO SECTION --}}
    <div class="pastel-hero shadow-sm mb-5">
        <div class="row align-items-center">

            <div class="col-md-6">
                <h1 class="fw-bold pastel-hero-title mb-2">
                    Cute & Lovely Craft Collections ✨
                </h1>

                <p class="pastel-hero-subtitle">
                    Handmade dengan cinta untuk momen spesialmu.
                    Mulai dari <strong>Bouquet</strong>, <strong>Frame</strong>, hingga <strong>Scrapframe</strong>.
                </p>

                <p class="pastel-hero-extra">
                    Temukan berbagai produk lucu dan aesthetic yang siap menghiasi harimu! 🌸🎀
                </p>

                <a href="#products" class="btn pastel-btn mt-2">
                    Explore Products
                </a>
            </div>

            <div class="col-md-6 text-center">
                {{-- Hero banner (rasio 4:3) --}}
                <img src="{{ asset('images/hero.png') }}" class="hero-img" alt="Cute Craft Banner">
            </div>

        </div>
    </div>

    {{-- DIVIDER --}}
    <div class="pastel-divider mb-4"></div>


    {{-- CATEGORIES SECTION --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="pastel-category-card text-center shadow-sm">
                <img src="{{ asset('images/categories/category-bouquet.jpg') }}" alt="Bouquet" class="cat-img mb-2">
                <h6 class="fw-bold">Bouquet</h6>
            </div>
        </div>

        <div class="col-md-4">
            <div class="pastel-category-card text-center shadow-sm">
                <img src="{{ asset('images/categories/category-frame.jpg') }}" alt="Frame" class="cat-img mb-2">
                <h6 class="fw-bold">Frame</h6>
            </div>
        </div>

        <div class="col-md-4">
            <div class="pastel-category-card text-center shadow-sm">
                <img src="{{ asset('images/categories/category-scrapframe.jpg') }}" alt="Scrapframe" class="cat-img mb-2">
                <h6 class="fw-bold">Scrapframe</h6>
            </div>
        </div>

    </div>

    {{-- DIVIDER --}}
    <div class="pastel-divider my-4"></div>



    {{-- TITLE --}}
    <h3 id="products" class="fw-bold pastel-section-title mb-4 text-center">
        Our Products
    </h3>


    {{-- FILTER --}}
    <form method="GET" class="row g-2 mb-4 align-items-end">

        <div class="col-md-3">
            <input type="text" name="q" class="form-control pastel-input" placeholder="Search..."
                value="{{ request('q') }}">
        </div>

        <div class="col-md-2">
            <input type="number" name="min_price" class="form-control pastel-input" placeholder="Min Price"
                value="{{ request('min_price') }}">
        </div>

        <div class="col-md-2">
            <input type="number" name="max_price" class="form-control pastel-input" placeholder="Max Price"
                value="{{ request('max_price') }}">
        </div>

        <div class="col-md-2">
            <select name="category" class="form-select pastel-input">
                <option value="">All Category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="sort" class="form-select pastel-input">
                <option value="">Sort By</option>
                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price Low</option>
                <option value="price_desc"{{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price High</option>
            </select>
        </div>

        <div class="col-md-1 d-grid">
            <button class="btn pastel-btn">Filter</button>
        </div>

    </form>



    {{-- PRODUCT GRID --}}
    <div class="row g-3">
        @forelse ($products as $product)
            <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                <div class="pastel-card shadow-sm p-2">

                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="pastel-thumb-img mb-2"
                            alt="{{ $product->name }}">
                    @else
                        <div class="pastel-thumb mb-2">
                            <span>{{ $product->id }}</span>
                        </div>
                    @endif

                    <div class="text-center px-2">
                        <h6 class="fw-bold text-dark mb-1">{{ $product->name }}</h6>

                        <span class="badge pastel-badge mb-1">
                            {{ $product->category->name ?? 'No Category' }}
                        </span>

                        <p class="text-muted pastel-desc mb-2">
                            {{ Str::limit($product->description, 80) }}
                        </p>

                        <h6 class="fw-bold text-danger mb-2">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </h6>

                        <div class="d-flex gap-1">
                            <a href="{{ route('products.show', $product->id) }}"
                                class="btn btn-sm pastel-outline w-50">Show</a>
                            <a href="{{ route('products.edit', $product->id) }}"
                                class="btn btn-sm pastel-warning w-50">Edit</a>
                            {{-- <a href="{{ route('cart.add', $product->id) }}" class="btn btn-sm pastel-btn w-100 mt-2">+ Add to Cart</a> --}}
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="w-33"
                                onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100">Delete</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('cart.add', $product->id) }}">
                            @csrf
                            <button class="btn btn-sm pastel-btn w-100 mt-2">
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

                        <div class="dropdown mt-2">
    <button class="btn btn-sm pastel-outline w-100 dropdown-toggle" 
            type="button" 
            data-bs-toggle="dropdown">
        📤 Bagikan
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" 
               href="https://wa.me/?text={{ urlencode($product->name . ' - ' . route('products.show', $product->id)) }}" 
               target="_blank">
                💚 WhatsApp
            </a>
        </li>
        <li>
            <a class="dropdown-item" 
               href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('products.show', $product->id)) }}" 
               target="_blank">
                💙 Facebook
            </a>
        </li>
        <li>
            <a class="dropdown-item" 
               href="https://twitter.com/intent/tweet?text={{ urlencode($product->name) }}&url={{ urlencode(route('products.show', $product->id)) }}" 
               target="_blank">
                🐦 Twitter
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" 
               href="#" 
               onclick="copyProductLink('{{ route('products.show', $product->id) }}'); return false;">
                🔗 Copy Link
            </a>
        </li>
    </ul>
</div>

                    </div>

                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="card p-4">No products found.</div>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-4 pastel-pagination">
        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
    @once
<script>
function copyProductLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('✅ Link berhasil disalin!');
    });
}
</script>
@endonce
@endsection
