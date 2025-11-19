<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class SkuGenerator
{
    private const CATEGORY_CODE_MAP = [
        'GPU' => 'GPU',
        'CPU' => 'CPU',
        'SSD' => 'SSD',
        'RAM' => 'RAM',
        'PSU' => 'PSU',
        'MOTHERBOARD' => 'MB',
        'AIO COOLER' => 'COOL',
        'PC' => 'PC',
    ];

    public static function make(): self
    {
        return new self();
    }

    public function generateUsingContext(
        string $productName,
        ?string $categoryName = null,
        ?string $brandName = null,
        ?string $ignoreProductId = null
    ): string {
        $categoryCode = $this->resolveCategoryCode($categoryName);
        $brandCode = $this->resolveBrandCode($brandName);
        $modelCode = $this->resolveModelCode($productName, $brandName);

        $base = collect([$categoryCode, $brandCode, $modelCode])
            ->filter()
            ->implode('-');

        if ($base === '') {
            $base = 'SKU';
        }

        $counter = $this->nextCounter($base, $ignoreProductId);

        return $base.'-'.$counter;
    }

    protected function resolveCategoryCode(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return 'GEN';
        }

        $upper = Str::upper(trim($name));

        foreach (self::CATEGORY_CODE_MAP as $pattern => $code) {
            if (Str::contains($upper, $pattern)) {
                return $code;
            }
        }

        $sanitized = preg_replace('/[^A-Z0-9]/', '', $upper) ?: '';
        $sanitized = substr($sanitized, 0, 4);

        return $sanitized !== '' ? $sanitized : 'GEN';
    }

    protected function resolveBrandCode(?string $name): string
    {
        if ($name === null || trim($name) === '') {
            return 'NOBRAND';
        }

        $sanitized = preg_replace('/[^A-Z0-9]/', '', Str::upper($name)) ?: '';
        $sanitized = substr($sanitized, 0, 12);

        return $sanitized !== '' ? $sanitized : 'NOBRAND';
    }

    protected function resolveModelCode(string $productName, ?string $brandName = null): string
    {
        $model = Str::upper($productName);

        if ($brandName) {
            $brandUpper = Str::upper($brandName);
            $pattern = '/^'.preg_quote($brandUpper, '/').'\s+/';
            $model = preg_replace($pattern, '', $model) ?? $model;
        }

        $model = preg_replace('/[^A-Z0-9]/', '', $model) ?: '';
        $model = substr($model, 0, 24);

        return $model !== '' ? $model : 'MODEL';
    }

    protected function nextCounter(string $base, ?string $ignoreProductId): string
    {
        $existingSkus = Product::query()
            ->select('sku')
            ->where('sku', 'like', $base.'-%')
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->pluck('sku');

        $maxCounter = 0;

        foreach ($existingSkus as $sku) {
            $suffix = Str::after($sku, $base.'-');
            if ($suffix !== '' && ctype_digit($suffix)) {
                $maxCounter = max($maxCounter, (int) $suffix);
            }
        }

        $next = $maxCounter + 1;
        $length = $next >= 100 ? strlen((string) $next) : 2;

        return str_pad((string) $next, $length, '0', STR_PAD_LEFT);
    }
}
