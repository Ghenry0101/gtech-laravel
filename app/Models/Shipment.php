<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_code',
        'courier_service_code',
        'courier_name',
        'courier_service',
        'tracking_id',
        'biteship_order_id',
        'shipping_cost',
        'status',
        'estimation_days',
        'shipped_at',
        'delivered_at',
        'rate_payload',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'rate_payload' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
