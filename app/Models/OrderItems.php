<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'qty',
        'price',
        'subtotal',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->price * $item->qty;
        });
    }
}
