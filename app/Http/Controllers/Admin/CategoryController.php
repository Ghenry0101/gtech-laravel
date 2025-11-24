<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $categories = $this->categories->paginate($search);

        return view('admin.barang.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated(), $request->boolean('is_active'));

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil ditambahkan.'));
    }

    public function edit(Category $category): View
    {
        return view('admin.barang.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated(), $request->boolean('is_active'));

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil diperbarui.'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if (! $this->categories->delete($category)) {
            return back()->withErrors([
                'category' => __('Kategori tidak dapat dihapus karena masih dipakai produk.'),
            ]);
        }

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil dihapus.'));
    }
}
