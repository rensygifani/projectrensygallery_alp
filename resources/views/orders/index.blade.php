@extends('layouts.app')

@section('content')

    <h3 class="fw-bold mb-3">📦 My Orders</h3>

    <a href="{{ route('products') }}" class="btn pastel-outline mb-4">
        ← Back to Products
    </a>

    @if ($orders->isEmpty())
        <div class="card p-4 text-center pastel-form-card">
            <div class="fs-1 mb-2">📭</div>
            <div class="fw-semibold">Belum ada pesanan</div>
            <div class="text-muted small">
                Yuk mulai belanja produk favoritmu
            </div>
        </div>
    @else
        @foreach ($orders as $order)
            <div class="card p-4 mb-4 shadow-sm pastel-form-card">

                {{-- HEADER ORDER --}}
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded pastel-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3">📦</div>
                        <div>
                            <div class="fw-bold fs-5">
                                Order #{{ $order->order_code ?? $order->id }}
                            </div>
                            <div class="text-muted small">
                                {{ $order->created_at->format('d M Y • H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="small text-muted">Status</div>
                        <span
                            class="badge 
                {{ $order->status === 'paid' ? 'bg-success' : ($order->status === 'pending' ? 'bg-warning' : 'bg-danger') }}">
                            {{ strtoupper($order->status) }}
                        </span>
                    </div>
                </div>

                <hr class="my-3">

                {{-- TOTAL --}}
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">Total Pembayaran</span>
                    <span class="fw-bold text-success">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                </div>

                {{-- PAYMENT METHOD --}}
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-semibold">Metode Pembayaran</span>
                    <span class="badge pastel-badge">
                        {{ $order->payment_method ? ucfirst($order->payment_method) : 'Belum Dipilih' }}
                    </span>
                </div>

                {{-- PAY AGAIN (PENDING ONLY) --}}
                {{-- @if ($order->status === 'pending')
                    <button class="btn pastel-primary w-100 mb-3"
                        onclick="window.location.href='{{ route('orders.pay', $order->id) }}'">
                        💳 Bayar Sekarang
                    </button>
                @endif --}}

                {{-- PAYMENT STATUS --}}
                @if ($order->status === 'pending')
                    @php
                        $totalMinutes = 120; // 2 jam = 120 menit
                        $minutesSinceCreated = $order->created_at->diffInMinutes(now());
                        $remainingMinutes = max(0, $totalMinutes - $minutesSinceCreated);
                        $percentage = min(100, ($remainingMinutes / $totalMinutes) * 100);

                        $hours = floor($remainingMinutes / 60);
                        $minutes = $remainingMinutes % 60;

                        $isCritical = $remainingMinutes < 15;
                        $isUrgent = $remainingMinutes < 30;
                    @endphp

                    @if ($remainingMinutes > 0)
                        {{-- COUNTDOWN CARD --}}
                        <div class="card mb-3 border-{{ $isCritical ? 'danger' : ($isUrgent ? 'warning' : 'info') }}">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if ($isCritical)
                                            <i class="bi bi-fire text-danger fs-3"></i>
                                        @elseif($isUrgent)
                                            <i class="bi bi-exclamation-triangle text-warning fs-3"></i>
                                        @else
                                            <i class="bi bi-clock text-info fs-3"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Batas Waktu Pembayaran</small>
                                            <span
                                                class="badge bg-{{ $isCritical ? 'danger' : ($isUrgent ? 'warning' : 'info') }}">
                                                {{ $hours }}j {{ $minutes }}m
                                            </span>
                                        </div>

                                        {{-- PROGRESS BAR --}}
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar 
                                bg-{{ $isCritical ? 'danger' : ($isUrgent ? 'warning' : 'success') }}"
                                                role="progressbar" style="width: {{ $percentage }}%">
                                            </div>
                                        </div>

                                        {{-- WARNING MESSAGE --}}
                                        @if ($isCritical)
                                            <small class="text-danger d-block mt-2">
                                                <i class="bi bi-exclamation-circle"></i>
                                                SEGERA BAYAR! Waktu hampir habis
                                            </small>
                                        @elseif($isUrgent)
                                            <small class="text-warning d-block mt-2">
                                                <i class="bi bi-clock-history"></i>
                                                Waktu pembayaran akan segera habis
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PAYMENT BUTTON --}}
                        <a href="{{ route('orders.pay', $order) }}" class="btn pastel-primary w-100 mb-3">
                            <i class="bi bi-credit-card me-2"></i> Bayar Sekarang
                        </a>
                    @else
                        {{-- ORDER EXPIRED --}}
                        <div class="alert alert-warning mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold">⚠️ Order Kadaluarsa</div>
                                    <small>Batas waktu pembayaran telah habis</small>
                                </div>
                            </div>
                        </div>

                        {{-- CANCEL BUTTON --}}
                        {{-- <form method="POST" action="{{ route('orders.cancel', $order) }}" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-2"></i> Batalkan Order
                            </button>
                        </form> --}}

                        {{-- CANCEL BUTTON untuk order yang expired --}}
                        @if ($remainingMinutes <= 0)
                            <form method="POST" action="{{ route('orders.cancel', $order) }}" class="d-inline w-100"
                                onsubmit="return confirm('⚠️ Yakin ingin membatalkan pesanan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i> Batalkan Order
                                </button>
                            </form>
                        @endif

                        {{-- ATAU untuk order yang masih pending tapi user ingin cancel manual --}}
                        @if ($order->status === 'pending' && $remainingMinutes > 0)
                            <form method="POST" action="{{ route('orders.cancel', $order) }}" class="d-inline"
                                onsubmit="return confirm('⚠️ Yakin ingin membatalkan pesanan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </button>
                            </form>
                        @endif
                    @endif
                @elseif($order->status === 'paid')
                    <div class="alert alert-success text-center mb-3">
                        <i class="bi bi-check-circle me-2"></i> ✅ Pembayaran Berhasil
                    </div>
                @elseif($order->status === 'canceled' || $order->status === 'expired')
                    <div class="alert alert-secondary text-center mb-3">
                        <i class="bi bi-clock-history me-2"></i> ⏰ Order {{ ucfirst($order->status) }}
                    </div>
                @endif

                {{-- PRODUCT LIST --}}
                <div class="mt-3">
                    <div class="fw-semibold mb-2">🛍 Produk</div>

                    <div class="list-group list-group-flush">
                        @foreach ($order->items as $item)
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

                                        {{-- REVIEW BUTTON (HANYA UNTUK ORDER YANG SUDAH DIBAYAR) --}}
                                        @if ($order->status === 'paid')
                                            @if (!$item->review)
                                                <button class="btn btn-sm pastel-primary mt-2" data-bs-toggle="modal"
                                                    data-bs-target="#reviewModal{{ $item->id }}">
                                                    ⭐ Review
                                                </button>
                                            @else
                                                <span class="badge pastel-badge mt-2">
                                                    Sudah direview
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @endforeach

        {{-- MODAL REVIEW (DI LUAR LOOP ORDER) --}}
        @foreach ($orders as $order)
            @if ($order->status === 'paid')
                @foreach ($order->items as $item)
                    @if (!$item->review)
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
                                                    @for ($i = 5; $i >= 1; $i--)
                                                        <option value="{{ $i }}">{{ $i }} ⭐
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label fw-semibold">Komentar</label>
                                                <textarea name="comment" class="form-control" rows="3" placeholder="Tulis pengalaman belanja kamu..."></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn pastel-outline" data-bs-dismiss="modal">
                                                Batal
                                            </button>
                                            <button type="submit" class="btn pastel-primary">
                                                Kirim Review
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach
        {{-- END MODAL --}}
    @endif

@endsection
