@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">{{ isset($coupon) ? 'Edit Kupon' : 'Buat Kupon Baru' }}</h2>
        <a href="{{ route('coupons.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ isset($coupon) ? route('coupons.update', $coupon) : route('coupons.store') }}" 
                  method="POST">
                @csrf
                @if(isset($coupon))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kode Kupon <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="code" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   value="{{ old('code', $coupon->code ?? '') }}" 
                                   placeholder="Contoh: DISKON50"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kode harus unik dan mudah diingat</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kupon <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $coupon->name ?? '') }}" 
                                   placeholder="Contoh: Diskon 50% Spesial"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Deskripsi kupon (opsional)">{{ old('description', $coupon->description ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipe Diskon <span class="text-danger">*</span></label>
                            <select name="type" 
                                    class="form-select @error('type') is-invalid @enderror" 
                                    required>
                                <option value="percentage" {{ old('type', $coupon->type ?? '') == 'percentage' ? 'selected' : '' }}>
                                    Persentase (%)
                                </option>
                                <option value="fixed" {{ old('type', $coupon->type ?? '') == 'fixed' ? 'selected' : '' }}>
                                    Fixed Amount (Rp)
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nilai Diskon <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="value" 
                                   step="0.01" 
                                   class="form-control @error('value') is-invalid @enderror" 
                                   value="{{ old('value', $coupon->value ?? '') }}" 
                                   placeholder="Contoh: 50 atau 25000"
                                   required>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Untuk persentase: 10 = 10%, untuk fixed: 25000 = Rp 25.000</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Minimal Pembelian</label>
                            <input type="number" 
                                   name="min_purchase" 
                                   step="0.01" 
                                   class="form-control" 
                                   value="{{ old('min_purchase', $coupon->min_purchase ?? '') }}"
                                   placeholder="Contoh: 100000">
                            <small class="text-muted">Kosongkan jika tidak ada minimal pembelian</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Maksimal Diskon</label>
                            <input type="number" 
                                   name="max_discount" 
                                   step="0.01" 
                                   class="form-control" 
                                   value="{{ old('max_discount', $coupon->max_discount ?? '') }}"
                                   placeholder="Contoh: 50000">
                            <small class="text-muted">Kosongkan jika tidak ada batas maksimal</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Limit Penggunaan Total</label>
                            <input type="number" 
                                   name="usage_limit" 
                                   class="form-control" 
                                   value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                                   placeholder="Contoh: 100">
                            <small class="text-muted">Berapa kali total kupon bisa digunakan (kosongkan = unlimited)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Limit Per User</label>
                            <input type="number" 
                                   name="per_user_limit" 
                                   class="form-control" 
                                   value="{{ old('per_user_limit', $coupon->per_user_limit ?? '') }}"
                                   placeholder="Contoh: 3">
                            <small class="text-muted">Berapa kali satu user bisa pakai kupon ini</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   name="start_date" 
                                   class="form-control @error('start_date') is-invalid @enderror" 
                                   value="{{ old('start_date', isset($coupon) ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}" 
                                   required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Berakhir <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   name="end_date" 
                                   class="form-control @error('end_date') is-invalid @enderror" 
                                   value="{{ old('end_date', isset($coupon) ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}" 
                                   required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       class="form-check-input" 
                                       {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold">
                                    Aktifkan Kupon
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn pastel-btn">
                        <i class="bi bi-save"></i> {{ isset($coupon) ? 'Update Kupon' : 'Simpan Kupon' }}
                    </button>
                    <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection