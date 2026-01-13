@extends('layouts.app')

@section('content')
<h3 class="fw-bold mb-3">💳 Pembayaran</h3>

<div class="card p-4">
    <div class="mb-3">
        <label>Nama</label>
        <input type="text" id="name" class="form-control" value="{{ auth()->user()->name }}">
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" id="email" class="form-control" value="{{ auth()->user()->email }}">
    </div>

    <div class="mb-3">
        <label>Total Bayar</label>
        <input type="number" id="amount" class="form-control" value="50000">
    </div>

    <button id="pay-button" class="btn pastel-btn">
        Bayar Sekarang
    </button>
</div>

{{-- Midtrans Snap --}}
<script
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
document.getElementById('pay-button').addEventListener('click', function () {
    fetch('{{ route("midtrans.token") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            amount: document.getElementById('amount').value
        })
    })
    .then(res => res.json())
    .then(data => {
        window.snap.pay(data.snap_token, {
            onSuccess: function(result) {
                alert('Pembayaran berhasil');
                console.log(result);
            },
            onPending: function(result) {
                alert('Menunggu pembayaran');
                console.log(result);
            },
            onError: function(result) {
                alert('Pembayaran gagal');
                console.log(result);
            }
        });
    });
});
</script>
@endsection
