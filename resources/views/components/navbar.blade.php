<nav class="navbar pastel-navbar shadow-sm py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('products') }}">
            <div class="brand-badge">✿</div>
            <div>
                <div class="fw-bold pastel-brand" style="font-size:18px;">Rensy Gallery</div>
                <div class="small text-muted" style="font-size:12px;">Bouquet • Frame • Scrapframe</div>
            </div>
        </a>

        <div class="d-flex gap-2 align-items-center">

            {{-- JIKA BELUM LOGIN --}}
            @guest
                <a href="{{ route('login') }}" class="btn pastel-outline">
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn pastel-btn">
                    Register
                </a>
            @endguest

            {{-- JIKA SUDAH LOGIN --}}
            @auth
                <a href="{{ route('products.create') }}" class="btn pastel-btn">
                    + Add Product
                </a>
                <a href="{{ route('cart') }}" class="btn pastel-outline">
                    🛒 Cart
                </a>

                <a href="{{ route('wishlist') }}" class="btn pastel-outline">
                    ❤️ Wishlist
                </a>

                <a href="{{ route('coupons.index') }}" class="btn pastel-outline">
                    🎟️ Kupon
                </a>

                <a href="{{ route('orders') }}" class="btn pastel-outline">
                    Orders
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-danger">
                        Logout
                    </button>
                </form>
            @endauth

        </div>

    </div>
</nav>
