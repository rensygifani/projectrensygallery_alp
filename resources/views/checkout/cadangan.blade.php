@extends('layouts.app')

@section('content')
<h3 class="fw-bold mb-3">Checkout</h3>

{{-- ================= CART ================= --}}
@foreach($items as $item)
<input type="hidden" name="items[]" value="{{ $item->id }}">

<div class="card p-3 mb-2 d-flex flex-row gap-3 align-items-center">
    <img src="{{ asset('storage/'.$item->product->image) }}" width="70" class="rounded">

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

<h5 class="fw-bold text-end mt-3">
    Subtotal: Rp <span id="subtotal">{{ $total }}</span>
</h5>

<hr>

{{-- ================= ALAMAT ================= --}}
<div class="mb-3">
    <label>Alamat Lengkap</label>
    <textarea class="form-control" id="address" required></textarea>
</div>

{{-- ================= ONGKIR ================= --}}
<div class="card p-3 mb-3">
    <h5 class="fw-bold mb-3">🚚 Pengiriman</h5>

    <select id="province" class="form-control mb-2">
        <option value="">Pilih Provinsi</option>
        @foreach($provinces as $province)
            <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
        @endforeach
    </select>

    <select id="city" class="form-control mb-2"></select>
    <select id="district" class="form-control mb-2"></select>

    <div class="mb-2">
        <label>Kurir</label><br>
        <input type="radio" name="courier" value="jne"> JNE
        <input type="radio" name="courier" value="jnt"> J&T
        <input type="radio" name="courier" value="sicepat"> SiCepat
    </div>

    <button type="button" id="cekOngkir" class="btn btn-outline-primary">
        Cek Ongkir
    </button>

    <div id="ongkirResult" class="mt-3"></div>
</div>

{{-- ================= TOTAL ================= --}}
<h4 class="fw-bold text-end">
    Total Bayar: Rp <span id="grandTotal">{{ $total }}</span>
</h4>

<button id="pay-button" class="btn pastel-btn w-100 mt-3">
    Bayar Sekarang
</button>

{{-- ================= MIDTRANS ================= --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
let ongkir = 0;
let subtotal = {{ $total }};

/* PROVINSI → KOTA */
document.getElementById('province').addEventListener('change', function () {
    fetch(`/cities/${this.value}`)
        .then(res => res.json())
        .then(data => {
            city.innerHTML = '<option>Pilih Kota</option>';
            data.forEach(i => city.innerHTML += `<option value="${i.id}">${i.name}</option>`);
        });
});

/* KOTA → KECAMATAN */
document.getElementById('city').addEventListener('change', function () {
    fetch(`/districts/${this.value}`)
        .then(res => res.json())
        .then(data => {
            district.innerHTML = '<option>Pilih Kecamatan</option>';
            data.forEach(i => district.innerHTML += `<option value="${i.id}">${i.name}</option>`);
        });
});

/* CEK ONGKIR (SIMULASI HASIL) */
document.getElementById('cekOngkir').addEventListener('click', function () {
    ongkir = 20000; // nanti dari API
    document.getElementById('ongkirResult').innerHTML =
        `<strong>Ongkir: Rp ${ongkir}</strong>`;

    document.getElementById('grandTotal').innerText = subtotal + ongkir;
});

/* MIDTRANS */
document.getElementById('pay-button').addEventListener('click', function () {
    fetch('/midtrans/token', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            total: subtotal + ongkir
        })
    })
    .then(res => res.json())
    .then(data => window.snap.pay(data.token));
});
</script>
@endsection
