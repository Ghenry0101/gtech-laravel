<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, HasUlids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'tracking_code',
        'full_address',
        'subtotal_amount',
        'shipping_cost',
        'total_amount',
        'order_status',
        'notes',
        'order_number',
        'payment_method',
        'order_time',
        'paid_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
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
        $subtotal = (float) ($this->subtotal_amount ?? 0);
        $shipping = (float) ($this->shipping_cost ?? 0);

        return (float) ($this->total_amount ?: ($subtotal + $shipping));
    }
}
