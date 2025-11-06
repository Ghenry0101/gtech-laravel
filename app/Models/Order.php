<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'subtotal',
        'shipping_cost',
        'total',
        'status',
        'note',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate order number
        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'GTECH-' . strtoupper(Str::random(8));
            }
        });
    }

    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relasi ke alamat
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // relasi ke item-item pesanan
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // total hitung otomatis
    public function getGrandTotalAttribute()
    {
        return $this->subtotal + $this->shipping_cost;
    }
}
