<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'address',
        'payment_method',
        'subtotal',
        'discount',        // ✅ TAMBAH
        'ongkir',
        'total',
        'status',
        'courier',
        'coupon_id',       // ✅ TAMBAH
        'payment_status',
        'payment_time',
        'midtrans_transaction_id',
        'midtrans_response'
    ];

    protected $casts = [
        'payment_time' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(\App\Models\Coupon::class);
    }
}