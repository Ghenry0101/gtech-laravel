<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(?string $category = null)
    {
        $categories = [
            ['slug' => 'cpu',        'name' => 'CPU',            'icon' => 'images/CPU-icon.png'],
            ['slug' => 'mobo',       'name' => 'MOTHER BOARD',   'icon' => 'images/mobo-icon.png'],
            ['slug' => 'psu',        'name' => 'POWER SUPPLY',   'icon' => 'images/PSU-icon.png'],
            ['slug' => 'ram',        'name' => 'RAM',            'icon' => 'images/RAM-icon.png'],
            ['slug' => 'hdd',        'name' => 'HARD DRIVE',     'icon' => 'images/HDD-icon.png'],
            ['slug' => 'ssd',        'name' => 'SOLID STATE DRIVE', 'icon' => 'images/SSD-icon.png'],
            ['slug' => 'gpu',        'name' => 'GRAPHIC CARD',   'icon' => 'images/GPU-icon.png'],
            ['slug' => 'casing',     'name' => 'CASING',         'icon' => 'images/casing-icon.png'],
            ['slug' => 'prebuilt',   'name' => 'PREBUILT',       'icon' => 'images/prebuilt-icon.png'],
            ['slug' => 'office',     'name' => 'OFFICE',         'icon' => 'images/officePC-icon.png'],
            ['slug' => 'school',     'name' => 'SCHOOL',         'icon' => 'images/schoolPC-icon.png'],
        ];

        $all = [
            ['title' => 'Intel Core i5-12400F', 'price' => 2199000, 'status' => 'available',   'image' => 'images/CPU.png', 'category' => 'cpu'],
            ['title' => 'AMD Ryzen 5 5600',     'price' => 1999000, 'status' => 'available',   'image' => 'images/CPU.png', 'category' => 'cpu'],

            ['title' => 'ASUS B550-Plus',       'price' => 2499000, 'status' => 'available',   'image' => 'images/moboPC.png', 'category' => 'mobo'],

            ['title' => 'Corsair RM650e',       'price' => 1599000, 'status' => 'available',   'image' => 'images/PSU.png', 'category' => 'psu'],

            ['title' => 'DDR4 16GB 3200',       'price' => 699000,  'status' => 'available',   'image' => 'images/RAM.png', 'category' => 'ram'],

            ['title' => 'HDD 1TB 7200rpm',      'price' => 649000,  'status' => 'available',   'image' => 'images/HDD.png', 'category' => 'hdd'],
            ['title' => 'SSD 1TB NVMe',         'price' => 1199000, 'status' => 'available',   'image' => 'images/SSD.png', 'category' => 'ssd'],

            ['title' => 'MSI RTX 5080',         'price' => 11990000,'status' => 'available',   'image' => 'images/GPU.png', 'category' => 'gpu'],

            ['title' => 'ATX Tempered Glass',   'price' => 799000,  'status' => 'available',   'image' => 'images/casingPC.png', 'category' => 'casing'],

            ['title' => 'High End PC Build',    'price' => 15999000,'status' => 'unavailable', 'image' => 'images/PC.png', 'category' => 'prebuilt'],
            ['title' => 'Office PC Basic',      'price' => 4999000, 'status' => 'available',   'image' => 'images/officePC.png', 'category' => 'office'],
            ['title' => 'School Lab PC',        'price' => 3999000, 'status' => 'available',   'image' => 'images/schoolPC.png', 'category' => 'school'],
        ];

        $active = $category;
        $items  = $active
            ? array_values(array_filter($all, fn ($p) => $p['category'] === $active))
            : $all;

        return view('products.index', [
            'categories' => $categories,
            'items'      => $items,
            'active'     => $active,
        ]);
    }

    public function showBySlug(string $slug)
    {
        return $this->renderDetail($slug);
    }

    public function show(string $slug)
    {
        return $this->renderDetail($slug);
    }

    protected function renderDetail(string $slug)
    {
        $product = $this->resolveProduct($slug);

        return view('products.show', [
            'product' => $product,
        ]);
    }

    protected function resolveProduct(string $slug): array
    {
        $catalog = config('products.items', []);
        $target  = Str::lower($slug);

        $product = collect($catalog)->first(function ($item) use ($target) {
            $candidate = $item['slug'] ?? Str::slug($item['nama_produk'] ?? '');
            return Str::lower($candidate) === $target;
        });

        abort_if(!$product, 404);

        return $this->decorateProduct($product);
    }

    protected function decorateProduct(array $product): array
    {
        $imagePath = $product['gambar_produk'] ?? 'images/PC.png';
        $gallery   = collect($product['gallery'] ?? [])
            ->filter()
            ->map(fn ($path) => asset($path))
            ->values()
            ->all();

        $product['kategori_label'] = strtoupper($product['kategori'] ?? 'OTHERS');
        $product['harga_fmt']      = '$' . number_format((float)($product['harga'] ?? 0), 2);
        $product['status_label']   = ($product['status'] ?? '') === 'available' ? 'AVAILABLE' : 'UNAVAILABLE';
        $product['image_full']     = asset($imagePath);
        $product['gallery_full']   = $gallery;

        return $product;
    }
}
