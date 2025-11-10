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
        'total_amount',
        'shipping_cost',
        'grand_total',
        'order_status',
        'notes',
        'payment_method',
        'order_time',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_at' => 'datetime',
        'order_time' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (! $order->order_number) {
                $order->order_number = 'GTECH-'.strtoupper(Str::random(8));
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

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    // total hitung otomatis
    public function getComputedTotalAttribute(): float
    {
        return (float) ($this->grand_total ?: ($this->total_amount + $this->shipping_cost));
    }
}
