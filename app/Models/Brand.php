<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $brand): void {
            $brand->slug = $brand->slug ?: static::generateUniqueSlug($brand->name);
        });

        static::updating(function (self $brand): void {
            if ($brand->isDirty('name')) {
                $brand->slug = static::generateUniqueSlug($brand->name, $brand->getKey());
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::slug(Str::random(6));
        $slug = $baseSlug;
        $suffix = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
