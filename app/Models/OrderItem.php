<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'qty', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

public function review()
{
    return $this->hasOne(\App\Models\Review::class, 'order_item_id');
}


}
