<?php

namespace App\Services\Admin;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function paginate(string $search, int $perPage = 12): LengthAwarePaginator
    {
        return Brand::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?UploadedFile $logo): Brand
    {
        if ($logo) {
            $data['logo'] = $logo->store('brands', 'public');
        }

        return Brand::create($data);
    }

    public function update(Brand $brand, array $data, ?UploadedFile $logo): void
    {
        if ($logo) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $data['logo'] = $logo->store('brands', 'public');
        }

        $brand->update($data);
    }

    public function delete(Brand $brand): bool
    {
        if ($brand->products()->exists()) {
            return false;
        }

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return true;
    }
}

