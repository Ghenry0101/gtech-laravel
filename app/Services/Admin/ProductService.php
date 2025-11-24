<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\SkuGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function paginate(string $search, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function formSelections(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function create(array $data, bool $isActive, bool $discountActive, ?UploadedFile $productImage): Product
    {
        $data['is_active'] = $isActive;
        $data = $this->preparePayload($data, null, $discountActive);

        if ($productImage) {
            $data['product_image'] = $productImage->store('products', 'public');
        }

        return Product::create($data);
    }

    public function update(Product $product, array $data, bool $isActive, bool $discountActive, ?UploadedFile $productImage): void
    {
        $data['is_active'] = $isActive;
        $data = $this->preparePayload($data, $product, $discountActive);

        if ($productImage) {
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }

            $data['product_image'] = $productImage->store('products', 'public');
        }

        $product->update($data);
    }

    public function delete(Product $product): void
    {
        if ($product->product_image) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();
    }

    public function generateSku(
        string $name,
        ?int $categoryId,
        ?int $brandId,
        ?string $productId = null
    ): string {
        $category = $this->findCategory($categoryId);
        $brand = $this->findBrand($brandId);

        $baseName = trim($name) !== '' ? $name : ($brand?->name ?? 'Produk');

        return SkuGenerator::make()->generateUsingContext(
            productName: $baseName,
            categoryName: $category?->name,
            brandName: $brand?->name,
            ignoreProductId: $productId,
        );
    }

    private function preparePayload(array $data, ?Product $existingProduct, bool $discountActive): array
    {
        $category = $this->findCategory($data['category_id'] ?? null);
        $brand = $this->findBrand($data['brand_id'] ?? null);

        $currentSku = $existingProduct?->sku;
        $data['sku'] = $this->determineSku($currentSku, $data['name'] ?? '', $category, $brand, $existingProduct?->getKey());

        $data = $this->prepareDiscountPayload($data, $discountActive);

        return $data;
    }

    private function findCategory($categoryId): ?Category
    {
        if ($categoryId === null) {
            return null;
        }

        return Category::find($categoryId);
    }

    private function findBrand($brandId): ?Brand
    {
        if ($brandId === null) {
            return null;
        }

        return Brand::find($brandId);
    }

    /**
     * Normalize and validate discount payload before persisting.
     *
     * @param  array<string, mixed>  $data
     * @throws \Illuminate\Validation\ValidationException
     * @return array<string, mixed>
     */
    private function prepareDiscountPayload(array $data, bool $hasDiscount): array
    {
        unset($data['discount_active']);

        if (! $hasDiscount) {
            $data['discount_percent'] = null;
            $data['discount_price'] = null;
            $data['discount_start'] = null;
            $data['discount_end'] = null;

            return $data;
        }

        $price = (float) ($data['price'] ?? 0);
        $percentInput = $data['discount_percent'] ?? null;
        $finalPriceInput = $data['discount_price'] ?? null;

        if ($percentInput === null && $finalPriceInput === null) {
            throw ValidationException::withMessages([
                'discount_percent' => __('Masukkan persentase atau harga setelah diskon.'),
            ]);
        }

        if ($percentInput !== null) {
            $percent = $this->clamp((float) $percentInput, 0, 100);
            $data['discount_percent'] = number_format($percent, 2, '.', '');
            $data['discount_price'] = number_format($this->priceAfterPercent($price, $percent), 2, '.', '');
        }

        if ($finalPriceInput !== null) {
            $finalPrice = $this->clamp((float) $finalPriceInput, 0, $price);
            $data['discount_price'] = number_format($finalPrice, 2, '.', '');

            if ($percentInput === null && $price > 0) {
                $percent = (($price - $finalPrice) / $price) * 100;
                $data['discount_percent'] = number_format($percent, 2, '.', '');
            }
        }

        $data['discount_start'] = $this->normalizeScheduleValue($data['discount_start'] ?? null);
        $data['discount_end'] = $this->normalizeScheduleValue($data['discount_end'] ?? null);

        return $data;
    }

    private function normalizeScheduleValue(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    private function priceAfterPercent(float $price, float $percent): float
    {
        $discounted = $price - ($price * $percent / 100);

        return max($discounted, 0);
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function determineSku(?string $input, string $productName, ?Category $category, ?Brand $brand, ?string $ignoreProductId = null): string
    {
        $normalized = $this->normalizeSku($input);

        if ($normalized) {
            return $normalized;
        }

        return SkuGenerator::make()->generateUsingContext(
            productName: $productName,
            categoryName: $category?->name,
            brandName: $brand?->name,
            ignoreProductId: $ignoreProductId,
        );
    }

    private function normalizeSku($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || str_starts_with(strtolower($value), '[object ')) {
            return null;
        }

        $normalized = preg_replace('/\s+/', '-', $value);
        if ($normalized === null) {
            $normalized = $value;
        }
        $normalized = strtoupper(trim($normalized));
        $normalized = preg_replace('/[^A-Z0-9\-]/', '', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : null;
    }
}
