<?php

namespace Database\Seeders; // ⭐ INI YANG PENTING!

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama jika ada
        Coupon::truncate();

        // Kupon 1: Diskon Persentase
        Coupon::create([
            'code' => 'DISKON50',
            'name' => 'Diskon 50%',
            'description' => 'Diskon 50% untuk pembelian minimal Rp 100.000',
            'type' => 'percentage',
            'value' => 50,
            'min_purchase' => 100000,
            'max_discount' => 50000,
            'usage_limit' => 100,
            'per_user_limit' => 3,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(1),
            'is_active' => true,
        ]);

        // Kupon 2: Fixed Amount
        Coupon::create([
            'code' => 'HEMAT25K',
            'name' => 'Hemat Rp 25.000',
            'description' => 'Potongan langsung Rp 25.000 untuk pembelian minimal Rp 50.000',
            'type' => 'fixed',
            'value' => 25000,
            'min_purchase' => 50000,
            'max_discount' => null,
            'usage_limit' => 50,
            'per_user_limit' => 1,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addWeeks(2),
            'is_active' => true,
        ]);

        // Kupon 3: Diskon Besar untuk Member Baru
        Coupon::create([
            'code' => 'WELCOME10',
            'name' => 'Welcome Discount 10%',
            'description' => 'Diskon 10% khusus member baru tanpa minimal pembelian',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => null,
            'max_discount' => 100000,
            'usage_limit' => null, // unlimited
            'per_user_limit' => 1,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'is_active' => true,
        ]);

        // Kupon 4: Flash Sale (sudah kadaluarsa untuk testing)
        Coupon::create([
            'code' => 'FLASHSALE',
            'name' => 'Flash Sale 70%',
            'description' => 'Diskon besar-besaran (EXPIRED)',
            'type' => 'percentage',
            'value' => 70,
            'min_purchase' => 200000,
            'max_discount' => 150000,
            'usage_limit' => 20,
            'per_user_limit' => 1,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(1), // sudah lewat
            'is_active' => false,
        ]);

        // Kupon 5: Gratis Ongkir
        Coupon::create([
            'code' => 'GRATIS15K',
            'name' => 'Gratis Ongkir Rp 15.000',
            'description' => 'Potongan Rp 15.000 untuk ongkir',
            'type' => 'fixed',
            'value' => 15000,
            'min_purchase' => 75000,
            'max_discount' => null,
            'usage_limit' => 200,
            'per_user_limit' => 5,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(2),
            'is_active' => true,
        ]);

        $this->command->info('✅ 5 kupon berhasil dibuat!');
    }
}