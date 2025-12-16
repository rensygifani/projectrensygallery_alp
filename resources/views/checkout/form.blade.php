@extends('layouts.app')

@section('content')

<h3 class="fw-bold mb-3">Checkout</h3>

<form method="POST" action="{{ route('checkout.process') }}">
@csrf

@foreach($items as $item)

{{-- PENTING: kirim ulang item id --}}
<input type="hidden" name="items[]" value="{{ $item->id }}">

<div class="card p-3 mb-2 d-flex flex-row gap-3 align-items-center">
    <img src="{{ asset('storage/'.$item->product->image) }}"
         width="70" class="rounded">

    <div class="flex-grow-1">
        <div class="fw-bold">{{ $item->product->name }}</div>
        <div class="small text-muted">
            {{ $item->qty }} × Rp {{ number_format($item->product->price,0,',','.') }}
        </div>
    </div>

    <div class="fw-bold">
        Rp {{ number_format($item->product->price * $item->qty,0,',','.') }}
    </div>
</div>

@endforeach

<h4 class="fw-bold text-end mt-3">
    Total: Rp {{ number_format($total,0,',','.') }}
</h4>

<div class="mb-3">
    <label>Alamat Pengiriman</label>
    <textarea name="address" class="form-control" required></textarea>
</div>

<div class="mb-3">
    <label>Metode Pembayaran</label>
    <select name="payment_method" class="form-control" required>
        <option value="Transfer">Transfer</option>
        <option value="COD">COD</option>
        <option value="E-Wallet">E-Wallet</option>
    </select>
</div>

<button class="btn pastel-btn w-100">
    Bayar Sekarang
</button>

</form>
@endsection
