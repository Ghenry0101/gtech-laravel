<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(string $search, int $perPage = 12): LengthAwarePaginator
    {
        return Category::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, bool $isActive): Category
    {
        $data['is_active'] = $isActive;

        return Category::create($data);
    }

    public function update(Category $category, array $data, bool $isActive): void
    {
        $data['is_active'] = $isActive;

        $category->update($data);
    }

    public function delete(Category $category): bool
    {
        if ($category->products()->exists()) {
            return false;
        }

        $category->delete();

        return true;
    }
}

