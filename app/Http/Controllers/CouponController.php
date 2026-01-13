<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Requests\StoreCouponRequest;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);
        return view('coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('coupons.create');
    }

    public function store(StoreCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil dibuat');
    }

    public function edit(Coupon $coupon)
    {
        return view('coupons.edit', compact('coupon'));
    }

    public function update(StoreCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil diupdate');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('coupons.index')
            ->with('success', 'Kupon berhasil dihapus');
    }

    // ✅ GANTI NAMA DARI validate() JADI validateCoupon()
    public function validateCoupon(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            $coupon = Coupon::where('code', $request->code)->first();

            if (!$coupon) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Kupon tidak ditemukan'
                ], 404);
            }

            if (!$coupon->isValid()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Kupon sudah tidak berlaku atau habis digunakan'
                ], 400);
            }

            if (!$coupon->canBeUsedBy(auth()->id())) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Anda sudah mencapai batas penggunaan kupon ini'
                ], 400);
            }

            $discount = $coupon->calculateDiscount($request->amount);

            if ($discount == 0) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Pembelian minimal Rp ' . number_format($coupon->min_purchase, 0, ',', '.')
                ], 400);
            }

            return response()->json([
                'valid' => true,
                'discount' => $discount,
                'final_amount' => $request->amount - $discount,
                'coupon' => [
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Coupon validation error: ' . $e->getMessage());
            
            return response()->json([
                'valid' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function apply(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            $coupon = Coupon::where('code', $request->code)->first();

            if (!$coupon || !$coupon->canBeUsedBy(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kupon tidak dapat digunakan'
                ], 400);
            }

            $discount = $coupon->calculateDiscount($request->amount);
            $coupon->incrementUsage(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Kupon berhasil digunakan',
                'discount' => $discount,
                'final_amount' => $request->amount - $discount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Coupon apply error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}