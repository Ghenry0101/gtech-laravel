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
            return collect(config('storefront.static_categories', []))
                ->map(function (array $category) {
                    $slug = $category['slug'];

                    return [
                        'id' => null,
                        'name' => $category['name'],
                        'slug' => $slug,
                        'icon' => $category['icon'] ?? self::iconFor($slug),
                        'background' => self::backgroundFor($slug),
                    ];
                })
                ->values()
                ->all();
        }

        $staticCategories = collect(config('storefront.static_categories', []))
            ->map(function (array $category) {
                $slug = $category['slug'];

                return [
                    'id' => null,
                    'name' => $category['name'],
                    'slug' => $slug,
                    'icon' => $category['icon'] ?? self::iconFor($slug),
                    'background' => self::backgroundFor($slug),
                ];
            })
            ->keyBy('slug');

        $dynamicCategories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                $slug = $category->slug;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $slug,
                    'icon' => self::iconFor($slug),
                    'background' => self::backgroundFor($slug),
                ];
            })
            ->keyBy('slug');

        $staticWithDynamic = $staticCategories->map(function (array $category) use ($dynamicCategories) {
            $slug = $category['slug'];

            if ($dynamicCategories->has($slug)) {
                $dynamic = $dynamicCategories->get($slug);

                return array_merge($category, [
                    'id' => $dynamic['id'],
                    'name' => $dynamic['name'] ?? $category['name'],
                    'icon' => $dynamic['icon'] ?? $category['icon'],
                    'background' => $dynamic['background'] ?? $category['background'],
                ]);
            }

            return $category;
        });

        $remainingDynamic = $dynamicCategories->reject(fn ($_value, $slug) => $staticCategories->has($slug));

        return $staticWithDynamic
            ->merge($remainingDynamic)
            ->values()
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
