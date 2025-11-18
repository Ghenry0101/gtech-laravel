<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'transaction_id',
        'payment_type',
        'va_number',
        'bank',
        'gross_amount',
        'payment_status',
        'fraud_status',
        'snap_token',
        'snap_redirect_url',
        'paid_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
