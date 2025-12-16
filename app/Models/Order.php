<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'address', 'payment_method', 'total'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
