<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
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
            $whereSql .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $likeTerm = "%{$search}%";
            $bindings = [$likeTerm, $likeTerm, $likeTerm];
        }

        $total = DB::selectOne(
            "SELECT COUNT(*) AS aggregate
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE 1 = 1 {$whereSql}",
            $bindings
        )->aggregate ?? 0;

        $records = collect(DB::select(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE 1 = 1 {$whereSql}
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($bindings, [$perPage, $offset])
        ));

        $products = new LengthAwarePaginator(
            $records,
            (int) $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.barang.products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = $this->getCategories();

        return view('admin.barang.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $now = CarbonImmutable::now();
        $slug = $this->generateUniqueSlug($data['name']);
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        DB::insert(
            "INSERT INTO products (category_id, name, slug, description, price, stock, weight, height, length, width, image, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['category_id'] ?? null,
                $data['name'],
                $slug,
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['weight'],
                $data['height'],
                $data['length'],
                $data['width'],
                $imagePath,
                $request->boolean('is_active'),
                $now,
                $now,
            ]
        );

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil ditambahkan.'));
    }

    public function edit(int $product): View
    {
        $record = $this->findProductOrFail($product);
        $categories = $this->getCategories();

        return view('admin.barang.products.edit', [
            'product' => $record,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateProductRequest $request, int $product): RedirectResponse
    {
        $existing = $this->findProductOrFail($product);
        $data = $request->validated();
        $now = CarbonImmutable::now();
        $imagePath = $existing->image;

        if ($request->hasFile('image')) {
            if ($existing->image) {
                Storage::disk('public')->delete($existing->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = $existing->name === $data['name']
            ? $existing->slug
            : $this->generateUniqueSlug($data['name'], $existing->id);

        DB::update(
            "UPDATE products
             SET category_id = ?, name = ?, slug = ?, description = ?, price = ?, stock = ?, weight = ?, height = ?, length = ?, width = ?, image = ?, is_active = ?, updated_at = ?
             WHERE id = ?",
            [
                $data['category_id'] ?? null,
                $data['name'],
                $slug,
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['weight'],
                $data['height'],
                $data['length'],
                $data['width'],
                $imagePath,
                $request->boolean('is_active'),
                $now,
                $existing->id,
            ]
        );

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil diperbarui.'));
    }

    public function destroy(int $product): RedirectResponse
    {
        $existing = $this->findProductOrFail($product);

        if ($existing->image) {
            Storage::disk('public')->delete($existing->image);
        }

        DB::delete("DELETE FROM products WHERE id = ?", [$existing->id]);

        return redirect()
            ->route('admin.barang.products.index')
            ->with('status', __('Produk berhasil dihapus.'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function getCategories(): Collection
    {
        return collect(DB::select("SELECT id, name FROM categories ORDER BY name"));
    }

    protected function findProductOrFail(int $id): object
    {
        $product = DB::selectOne(
            "SELECT * FROM products WHERE id = ?",
            [$id]
        );

        if (! $product) {
            throw new NotFoundHttpException(__('Produk tidak ditemukan.'));
        }

        return $product;
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::slug(Str::random(8));
        $slug = $baseSlug;
        $suffix = 1;

        while (true) {
            $query = "SELECT id FROM products WHERE slug = ?";
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
