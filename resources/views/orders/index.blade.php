@extends('layouts.app')

@section('content')
<h3 class="fw-bold mb-3">📦 My Orders</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-3">
    ← Back to Products
</a>

@if($orders->isEmpty())
    <div class="card p-4 text-center">
        Belum ada pesanan 📭
    </div>
@else
    @foreach($orders as $order)
    <div class="card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Order #{{ $order->id }}</strong>
            <span class="small text-muted">{{ $order->created_at->format('d M Y, H:i') }}</span>
        </div>

        <div class="mb-2">
            <strong>Total: </strong> Rp {{ number_format($order->total, 0, ',', '.') }}
        </div>

        <div class="mb-2">
            <strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}
        </div>

        <div>
            <strong>Products:</strong>
            <ul>
                @foreach($order->items as $item)
                    <li>{{ $item->product->name }} x {{ $item->quantity }} (Rp {{ number_format($item->price, 0, ',', '.') }})</li>
                @endforeach
            </ul>
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
