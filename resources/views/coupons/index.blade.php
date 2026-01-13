@extends('layouts.app')

@section('content')
<a href="{{ route('products') }}" class="btn pastel-outline mb-4">
    ← Back to Products
</a>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Daftar Kupon</h2>
        <a href="{{ route('coupons.create') }}" class="btn pastel-btn">
            <i class="bi bi-plus-circle"></i> Buat Kupon Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Min. Pembelian</th>
                            <th>Digunakan</th>
                            <th>Status</th>
                            <th>Periode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <code class="fs-6 fw-bold text-primary">{{ $coupon->code }}</code>
                            </td>
                            <td>{{ $coupon->name }}</td>
                            <td>
                                @if($coupon->type == 'percentage')
                                    <span class="badge bg-info">Persentase</span>
                                @else
                                    <span class="badge bg-success">Fixed</span>
                                @endif
                            </td>
                            <td>
                                @if($coupon->type == 'percentage')
                                    <strong>{{ $coupon->value }}%</strong>
                                @else
                                    <strong>Rp {{ number_format($coupon->value, 0, ',', '.') }}</strong>
                                @endif
                            </td>
                            <td>
                                @if($coupon->min_purchase)
                                    <small>Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}</small>
                                @else
                                    <small class="text-muted">Tidak ada</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $coupon->usage_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                </span>
                            </td>
                            <td>
                                @if($coupon->is_active && $coupon->isValid())
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle"></i> Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $coupon->start_date->format('d/m/Y') }}<br>
                                    s/d {{ $coupon->end_date->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('coupons.edit', $coupon) }}" 
                                       class="btn btn-outline-warning btn-sm" 
                                       title="Edit">
                                        {{-- <i class="bi bi-pencil"></i> --}}
                                        Edit
                                    </a>
                                    <form action="{{ route('coupons.destroy', $coupon) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus kupon ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-outline-danger btn-sm" 
                                                title="Hapus">
                                            {{-- <i class="bi bi-trash"></i> --}}
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-2">Belum ada kupon. <a href="{{ route('coupons.create') }}">Buat sekarang</a></p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
</div>
@endsection