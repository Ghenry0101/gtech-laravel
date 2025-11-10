<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_percent',
        'discount_price',
        'discount_start',
        'discount_end',
        'stock',
        'weight',
        'height',
        'length',
        'width',
        'product_image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
        'stock' => 'integer',
        'weight' => 'integer',
        'height' => 'integer',
        'length' => 'integer',
        'width' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = $product->slug ?: static::generateUniqueSlug($product->name);
        });

        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->getKey());
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::slug(Str::random(8));
        $slug = $baseSlug;
        $suffix = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    public function hasDiscountConfigured(): bool
    {
        return $this->discount_percent !== null || $this->discount_price !== null;
    }

    public function hasDiscountActive(): bool
    {
        if (! $this->hasDiscountConfigured()) {
            return false;
        }

        $now = now();

        if ($this->discount_start && $this->discount_start->isFuture()) {
            return false;
        }

        if ($this->discount_end && $this->discount_end->isPast()) {
            return false;
        }

        return true;
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->hasDiscountActive()) {
            if ($this->discount_price !== null) {
                return (float) $this->discount_price;
            }

            if ($this->discount_percent !== null) {
                $price = (float) $this->price;

                return (float) round($price - ($price * ((float) $this->discount_percent) / 100), 2);
            }
        }

        return (float) $this->price;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            OrderItem::class,
            'product_id',
            'order_item_id',
            'id',
            'id'
        );
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
