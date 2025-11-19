<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'rating',
        'title',
        'comment',
        'reviewed_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            OrderItem::class,
            'id',
            'id',
            'order_item_id',
            'product_id'
        );
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class)->orderBy('position');
    }
}
