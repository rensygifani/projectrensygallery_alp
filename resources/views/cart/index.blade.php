@extends('layouts.app')

@section('content')

<h3 class="fw-bold mb-3">🛒 Shopping Cart</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-3">
    ← Back to Products
</a>

@if($items->isEmpty())
    <div class="card p-4 text-center">
        🛒 Cart masih kosong
    </div>
@else

{{-- FORM CHECKOUT --}}
<form method="POST" action="{{ route('checkout.preview') }}">
@csrf

@php $total = 0; @endphp

@foreach($items as $item)
@php
    $subtotal = $item->product->price * $item->qty;
    $total += $subtotal;
@endphp

<div class="card p-3 mb-3 d-flex flex-row align-items-center gap-3">

    {{-- CHECKBOX --}}
    <input type="checkbox"
           name="items[]"
           value="{{ $item->id }}"
           class="form-check-input mt-0"
           checked>

    {{-- IMAGE --}}
    @if($item->product->image)
        <img src="{{ asset('storage/'.$item->product->image) }}"
             width="80" class="rounded">
    @else
        <div class="rounded bg-light d-flex align-items-center justify-content-center"
             style="width:80px;height:80px;">📦</div>
    @endif

    {{-- INFO --}}
    <div class="flex-grow-1">
        <div class="fw-bold">{{ $item->product->name }}</div>
        <div class="text-muted small">
            Rp {{ number_format($item->product->price,0,',','.') }}
        </div>
        <div class="fw-semibold">
            Subtotal: Rp {{ number_format($subtotal,0,',','.') }}
        </div>
    </div>

    {{-- QTY --}}
    <div class="d-flex align-items-center gap-1">

        <button type="button"
                class="btn btn-sm pastel-outline"
                onclick="document.getElementById('minus-{{ $item->id }}').submit()">
            ➖
        </button>

        <span class="fw-bold px-2">{{ $item->qty }}</span>

        <button type="button"
                class="btn btn-sm pastel-outline"
                onclick="document.getElementById('plus-{{ $item->id }}').submit()">
            ➕
        </button>
    </div>

    {{-- DELETE --}}
    <button type="button"
            class="btn btn-sm pastel-danger"
            onclick="document.getElementById('remove-{{ $item->id }}').submit()">
        🗑
    </button>

</div>

@endforeach

<hr>

<h4 class="fw-bold text-end">
    Total Sementara: Rp {{ number_format($total,0,',','.') }}
</h4>

<div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn pastel-btn px-4">
        Checkout →
    </button>
</div>

</form>

{{-- FORM TERSEMBUNYI --}}
@foreach($items as $item)

<form id="plus-{{ $item->id }}" method="POST" action="{{ route('cart.update',$item->id) }}" class="d-none">
    @csrf
    <input type="hidden" name="action" value="plus">
</form>

<form id="minus-{{ $item->id }}" method="POST" action="{{ route('cart.update',$item->id) }}" class="d-none">
    @csrf
    <input type="hidden" name="action" value="minus">
</form>

<form id="remove-{{ $item->id }}" method="POST" action="{{ route('cart.remove',$item->id) }}" class="d-none">
    @csrf
</form>

@endforeach

@endif

@endsection





{{-- @extends('layouts.app')

@section('content')

<h3 class="fw-bold mb-3">🛒 Shopping Cart</h3>

<a href="{{ route('products') }}" class="btn pastel-outline mb-3">
    ← Back to Products
</a>

@if($items->isEmpty())
    <div class="card p-4 text-center">
        🛒 Cart masih kosong
    </div>
@endif

@foreach($items as $item)
<div class="card p-3 mb-3">
    <strong>{{ $item->product->name }}</strong>

    <div class="small text-muted">
        Harga: Rp {{ number_format($item->product->price,0,',','.') }}
    </div>

    <div class="fw-semibold mt-1">
        Subtotal:
        Rp {{ number_format($item->product->price * $item->qty,0,',','.') }}
    </div>

    <form method="POST" action="{{ route('cart.update',$item->id) }}" class="d-flex gap-2 mt-2">
        @csrf
        <input type="number" name="qty" value="{{ $item->qty }}" min="1" class="form-control w-25">
        <button class="btn pastel-btn">Update</button>
    </form>

    <form method="POST" action="{{ route('cart.remove',$item->id) }}">
        @csrf
        <button class="btn btn-sm btn-danger mt-2">Remove</button>
    </form>
</div>
@endforeach

@if(!$items->isEmpty())
<h4 class="fw-bold mt-3">
    Total: Rp {{ number_format($total,0,',','.') }}
</h4>

<a href="{{ route('checkout') }}" class="btn pastel-btn mt-3">
    Checkout →
</a>
@endif

{{-- <h3 class="fw-bold">Shopping Cart</h3>

@forelse($items as $item)
<div class="card p-3 mb-2">
    <strong>{{ $item->product->name }}</strong>

    <form method="POST" action="{{ route('cart.update',$item->id) }}" class="d-flex gap-2 mt-2">
        @csrf
        <input type="number" name="qty" value="{{ $item->qty }}" min="1" class="form-control w-25">
        <button class="btn pastel-btn">Update</button>
    </form>

    <form method="POST" action="{{ route('cart.remove',$item->id) }}">
        @csrf
        <button class="btn btn-sm btn-danger mt-2 w-100">
            🗑 Remove
        </button>
    </form>
</div>
@empty
<div class="alert alert-warning text-center">
    🛒 Cart masih kosong
</div>
@endforelse

<h4>Total: Rp {{ number_format($total,0,',','.') }}</h4>

<a href="{{ route('checkout') }}" class="btn pastel-btn mt-3">Checkout</a> --}}
{{-- @endsection --}}
