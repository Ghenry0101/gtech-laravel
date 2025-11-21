<?php

namespace App\Models;

use App\Support\SkuGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
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
        'brand_id',
        'category_id',
        'sku',
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

            if (! $product->sku) {
                $product->sku = SkuGenerator::make()->generateUsingContext(
                    productName: $product->name,
                    categoryName: $product->categoryNameForSku(),
                    brandName: $product->brandNameForSku(),
                );
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->getKey());
            }

            $shouldRegenerateSku = $product->isDirty(['name', 'brand_id', 'category_id']) || ! $product->sku;

            if ($shouldRegenerateSku) {
                $product->sku = SkuGenerator::make()->generateUsingContext(
                    productName: $product->name,
                    categoryName: $product->categoryNameForSku(),
                    brandName: $product->brandNameForSku(),
                    ignoreProductId: $product->getKey(),
                );
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?string $ignoreId = null): string
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

    protected function categoryNameForSku(): ?string
    {
        if ($this->relationLoaded('category') && $this->category && (string) $this->category->getKey() === (string) $this->category_id) {
            return $this->category->name;
        }

        if (! $this->category_id) {
            return null;
        }

        return $this->category()
            ->withoutGlobalScopes()
            ->value('name');
    }

    protected function brandNameForSku(): ?string
    {
        if ($this->relationLoaded('brand') && $this->brand && (string) $this->brand->getKey() === (string) $this->brand_id) {
            return $this->brand->name;
        }

        if (! $this->brand_id) {
            return null;
        }

        return $this->brand()
            ->withoutGlobalScopes()
            ->value('name');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
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
