<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
