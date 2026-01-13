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

<form method="POST" action="{{ route('checkout.preview') }}">
@csrf

@foreach($items as $item)
@php
    $subtotal = $item->product->price * $item->qty;
@endphp

<div class="card p-3 mb-3 d-flex flex-row align-items-center gap-3">

    {{-- CHECKBOX --}}
    <input type="checkbox"
           name="items[]"
           value="{{ $item->id }}"
           class="form-check-input item-checkbox"
           data-id="{{ $item->id }}"
           data-price="{{ $item->product->price }}"
           data-qty="{{ $item->qty }}">

    {{-- IMAGE --}}
    <img src="{{ asset('storage/'.$item->product->image) }}"
         width="80" class="rounded">

    {{-- INFO --}}
    <div class="flex-grow-1">
        <div class="fw-bold">{{ $item->product->name }}</div>

        <div class="text-muted small">
            Harga: Rp {{ number_format($item->product->price,0,',','.') }}
        </div>

        <div class="small">
            Qty: <span class="fw-semibold">{{ $item->qty }}</span>
        </div>

        {{-- SUBTOTAL --}}
        <div class="fw-semibold text-success mt-1">
            Subtotal:
            Rp {{ number_format($subtotal,0,',','.') }}
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
    Total Sementara:
    <span id="totalText">Rp 0</span>
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

{{-- SCRIPT FINAL --}}
<script>
const storageKey = 'cart_checked_items';

function getCheckedItems() {
    return JSON.parse(localStorage.getItem(storageKey)) || [];
}

function saveCheckedItems(ids) {
    localStorage.setItem(storageKey, JSON.stringify(ids));
}

function updateTotal() {
    let total = 0;
    let checkedIds = [];

    document.querySelectorAll('.item-checkbox').forEach(cb => {
        if (cb.checked) {
            total += parseInt(cb.dataset.price) * parseInt(cb.dataset.qty);
            checkedIds.push(cb.dataset.id);
        }
    });

    saveCheckedItems(checkedIds);

    document.getElementById('totalText').innerText =
        'Rp ' + total.toLocaleString('id-ID');
}

// restore checkbox state
const saved = getCheckedItems();
document.querySelectorAll('.item-checkbox').forEach(cb => {
    if (saved.includes(cb.dataset.id)) {
        cb.checked = true;
    }
    cb.addEventListener('change', updateTotal);
});

updateTotal();
</script>

@endsection
