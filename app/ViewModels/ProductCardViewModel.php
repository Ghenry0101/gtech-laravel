<?php

namespace App\ViewModels;

use App\Models\Product;

class ProductCardViewModel
{
    /**
     * Transform a product model into an array consumable by the product grid.
     *
     * @return array<string, mixed>
     */
    public static function fromModel(Product $product): array
    {
        $imagePath = $product->product_image ? 'storage/'.$product->product_image : 'images/PC.png';
        $hasDiscount = $product->hasDiscountConfigured();
        $isDiscountActive = $product->hasDiscountActive();
        $plannedPrice = $product->discount_price;

        if ($plannedPrice === null && $product->discount_percent !== null) {
            $plannedPrice = round(
                (float) $product->price - ((float) $product->price * (float) $product->discount_percent / 100),
                2
            );
        }

        $price = $isDiscountActive && $plannedPrice !== null
            ? (float) $product->effective_price
            : (float) ($plannedPrice !== null ? $plannedPrice : $product->price);

        return [
            'id' => $product->id,
            'title' => $product->name,
            'slug' => $product->slug,
            'status' => $product->stock > 0 ? 'available' : 'unavailable',
            'price' => $price,
            'original_price' => (float) $product->price,
            'planned_price' => $plannedPrice !== null ? (float) $plannedPrice : null,
            'has_discount' => $hasDiscount,
            'is_discount_active' => $isDiscountActive,
            'discount_percent' => $product->discount_percent,
            'discount_start' => optional($product->discount_start)?->format('d M Y H:i'),
            'discount_end' => optional($product->discount_end)?->format('d M Y H:i'),
            'image_path' => $imagePath,
            'stock' => $product->stock,
        ];
    }

    /**
     * Transform a collection/array of products.
     *
     * @param  iterable<Product>  $products
     * @return array<int, array<string, mixed>>
     */
    public static function collection(iterable $products): array
    {
        return collect($products)
            ->map(static fn (Product $product) => self::fromModel($product))
            ->all();
    }
}

