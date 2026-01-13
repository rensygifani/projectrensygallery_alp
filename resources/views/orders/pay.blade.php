@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Bayar Order {{ $order->order_code }}</h1>
    <p>Status: {{ $order->status }}</p>
    <p>Total: Rp {{ number_format($order->total, 0, ',', '.') }}</p>

    {{-- Tombol bayar yang memanggil Midtrans Snap popup --}}
    <button id="pay-button" class="btn btn-sm pastel-btn">Bayar Sekarang</button>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
            snap.pay('{{ $snapToken }}', {
                onSuccess: function (result) {
                    // Bisa redirect ke halaman sukses dan update status
                    window.location.href = "{{ route('orders') }}";
                },
                onPending: function (result) {
                    alert("Pembayaran masih pending.");
                },
                onError: function (result) {
                    alert("Terjadi kesalahan pembayaran.");
                },
                onClose: function () {
                    alert('Anda menutup popup pembayaran tanpa menyelesaikan pembayaran.');
                }
            });
        });
    </script>
</div>
@endsection
