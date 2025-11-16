<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class CategoryMenu
{
    /**
     * Get all active categories formatted for navigation/tiles.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function active(): array
    {
        if (! Schema::hasTable('categories')) {
            return [];
        }

        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => self::iconFor($category->slug),
                'background' => self::backgroundFor($category->slug),
            ])
            ->all();
    }

    /**
     * Resolve icon path for a given category slug.
     */
    public static function iconFor(string $slug): string
    {
        $defaultIcon = config('storefront.default_category_icon', 'images/prebuilt-icon.png');
        $iconMap = config('storefront.category_icons', []);

        if (isset($iconMap[$slug])) {
            return $iconMap[$slug];
        }

        $underscored = str_replace('-', '_', $slug);
        if (isset($iconMap[$underscored])) {
            return $iconMap[$underscored];
        }

        $guessedIcon = 'images/'.$slug.'-icon.png';
        if (file_exists(public_path($guessedIcon))) {
            return $guessedIcon;
        }

        return $defaultIcon;
    }

    public static function backgroundFor(string $slug): string
    {
        $defaultBackground = config('storefront.category_backgrounds.default', 'images/PcBan.png');
        $backgroundMap = config('storefront.category_backgrounds', []);

        if (isset($backgroundMap[$slug])) {
            return $backgroundMap[$slug];
        }

        $underscored = str_replace('-', '_', $slug);
        if (isset($backgroundMap[$underscored])) {
            return $backgroundMap[$underscored];
        }

        $guessed = 'images/'.$slug.'.png';
        if (file_exists(public_path($guessed))) {
            return $guessed;
        }

        return $defaultBackground;
    }
}
