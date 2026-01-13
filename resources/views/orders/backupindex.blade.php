@extends('layouts.app')

@section('content')

<h3 class="fw-bold mb-3">📦 My Orders</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-4">
    ← Back to Products
</a>

@if($orders->isEmpty())
    <div class="card p-4 text-center pastel-form-card">
        <div class="fs-1 mb-2">📭</div>
        <div class="fw-semibold">Belum ada pesanan</div>
        <div class="text-muted small">
            Yuk mulai belanja produk favoritmu
        </div>
    </div>
@else

@foreach($orders as $order)
<div class="card p-4 mb-4 shadow-sm pastel-form-card">

    {{-- HEADER ORDER --}}
    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded pastel-header">
        <div class="d-flex align-items-center gap-3">
            <div class="fs-3">📦</div>
            <div>
                <div class="fw-bold fs-5">
                    Order #{{ $order->id }}
                </div>
                <div class="text-muted small">
                    {{ $order->created_at->format('d M Y • H:i') }}
                </div>
            </div>
        </div>

        <div class="text-end">
            <div class="small text-muted">Payment Method</div>
            <span class="badge pastel-badge">
                {{ ucfirst($order->payment_method) }}
            </span>
        </div>
    </div>

    <hr class="my-3">

    {{-- TOTAL ORDER --}}
    <div class="d-flex justify-content-between mb-3">
        <span class="fw-semibold">Total Pembayaran</span>
        <span class="fw-bold text-success">
            Rp {{ number_format($order->total, 0, ',', '.') }}
        </span>
    </div>

    {{-- PRODUCT LIST --}}
    <div class="mt-3">
        <div class="fw-semibold mb-2">🛍 Produk</div>

        <div class="list-group list-group-flush">
            @foreach($order->items as $item)
            <div class="list-group-item px-0">

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">
                            {{ $item->product->name }}
                        </div>

                        <div class="small text-muted">
                            Jumlah: {{ $item->qty }} ×
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="fw-semibold">
                            Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}
                        </div>

                        {{-- REVIEW BUTTON --}}
                        @if(!$item->review)
                            <button class="btn btn-sm pastel-primary mt-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#reviewModal{{ $item->id }}">
                                ⭐ Review
                            </button>
                        @else
                            <span class="badge pastel-badge mt-2">
                                Sudah direview
                            </span>
                        @endif
                    </div>
                </div>

            </div>

            {{-- MODAL REVIEW --}}
            <div class="modal fade" id="reviewModal{{ $item->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('review.store', $item->product) }}">
                        @csrf
                        <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                        <div class="modal-content pastel-form-card">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Review {{ $item->product->name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rating</label>
                                    <select name="rating" class="form-select" required>
                                        <option value="">Pilih Rating</option>
                                        @for($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }} ⭐</option>
                                        @endfor
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold">Komentar</label>
                                    <textarea name="comment"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Tulis pengalaman belanja kamu..."></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn pastel-outline" data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button class="btn pastel-primary">
                                    Kirim Review
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            {{-- END MODAL --}}

            @endforeach
        </div>
    </div>

</div>
@endforeach

@endif

@endsection





{{-- @extends('layouts.app')

@section('content')
<h3 class="fw-bold mb-3">📦 My Orders</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-3">
    ← Back to Products
</a>

@if($orders->isEmpty())
    <div class="card p-4 text-center">
        Belum ada pesanan 📭
    </div>
@endif

@foreach($orders as $order)
<div class="card p-3 mb-3">
    <strong>Total: Rp {{ number_format($order->total,0,',','.') }}</strong>
    <div class="small text-muted">{{ $order->payment_method }}</div>
</div>
@endforeach
@endsection --}}
