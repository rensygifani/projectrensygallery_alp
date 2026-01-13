<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . $this->coupon?->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode kupon harus diisi',
            'code.unique' => 'Kode kupon sudah digunakan',
            'name.required' => 'Nama kupon harus diisi',
            'type.required' => 'Tipe kupon harus dipilih',
            'type.in' => 'Tipe kupon harus percentage atau fixed',
            'value.required' => 'Nilai diskon harus diisi',
            'value.min' => 'Nilai diskon minimal 0',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai',
        ];
    }
}