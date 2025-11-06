<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $bindings = [];
        $whereSql = '';

        if ($search !== '') {
            $whereSql .= " AND (name LIKE ? OR slug LIKE ?)";
            $like = "%{$search}%";
            $bindings = [$like, $like];
        }

        $total = (int) (DB::selectOne(
            "SELECT COUNT(*) AS aggregate FROM categories WHERE 1 = 1 {$whereSql}",
            $bindings
        )->aggregate ?? 0);

        $records = collect(DB::select(
            "SELECT * FROM categories
             WHERE 1 = 1 {$whereSql}
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($bindings, [$perPage, $offset])
        ));

        $categories = new LengthAwarePaginator(
            $records,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.barang.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.barang.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->generateUniqueSlug($data['name']);
        $now = CarbonImmutable::now();

        DB::insert(
            "INSERT INTO categories (name, slug, description, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $request->boolean('is_active'),
                $now,
                $now,
            ]
        );

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil ditambahkan.'));
    }

    public function edit(int $category): View
    {
        $record = $this->findCategoryOrFail($category);

        return view('admin.barang.categories.edit', [
            'category' => $record,
        ]);
    }

    public function update(UpdateCategoryRequest $request, int $category): RedirectResponse
    {
        $existing = $this->findCategoryOrFail($category);
        $data = $request->validated();
        $now = CarbonImmutable::now();

        $slug = $existing->name === $data['name']
            ? $existing->slug
            : $this->generateUniqueSlug($data['name'], $existing->id);

        DB::update(
            "UPDATE categories
             SET name = ?, slug = ?, description = ?, is_active = ?, updated_at = ?
             WHERE id = ?",
            [
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $request->boolean('is_active'),
                $now,
                $existing->id,
            ]
        );

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil diperbarui.'));
    }

    public function destroy(int $category): RedirectResponse
    {
        $existing = $this->findCategoryOrFail($category);

        $productCount = DB::selectOne(
            "SELECT COUNT(*) AS aggregate FROM products WHERE category_id = ?",
            [$existing->id]
        )->aggregate ?? 0;

        if ($productCount > 0) {
            return back()->withErrors([
                'category' => __('Kategori tidak dapat dihapus karena masih dipakai oleh :count produk.', ['count' => $productCount]),
            ]);
        }

        DB::delete("DELETE FROM categories WHERE id = ?", [$existing->id]);

        return redirect()
            ->route('admin.barang.categories.index')
            ->with('status', __('Kategori berhasil dihapus.'));
    }

    protected function findCategoryOrFail(int $id): object
    {
        $category = DB::selectOne("SELECT * FROM categories WHERE id = ?", [$id]);

        if (! $category) {
            throw new NotFoundHttpException(__('Kategori tidak ditemukan.'));
        }

        return $category;
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::slug(Str::random(8));
        $slug = $baseSlug;
        $suffix = 1;

        while (true) {
            $query = "SELECT id FROM categories WHERE slug = ?";
            $bindings = [$slug];

            if ($ignoreId) {
                $query .= " AND id <> ?";
                $bindings[] = $ignoreId;
            }

            $exists = DB::selectOne($query, $bindings);

            if (! $exists) {
                return $slug;
            }

            $slug = $baseSlug.'-'.$suffix++;
        }
    }
}

