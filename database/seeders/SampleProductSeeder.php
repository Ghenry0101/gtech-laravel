<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SampleProductSeeder extends Seeder
{
    /**
     * Seed a handful of catalog items so related products can be demonstrated.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $catalog = [
            'ram' => [
                [
                    'name' => 'G.Skill Trident Z5 RGB 16GB DDR5',
                    'sku' => 'RAM-GSK-16',
                    'price' => 2250000,
                    'stock' => 12,
                    'description' => 'Dual channel kit, DDR5-6400 ideal for gaming rigs.',
                    'weight' => 180,
                    'length' => 13,
                    'width' => 1,
                    'height' => 4,
                    'discount_percent' => 10,
                ],
                [
                    'name' => 'Corsair Vengeance 32GB DDR4',
                    'sku' => 'RAM-COR-32',
                    'price' => 1950000,
                    'stock' => 9,
                    'description' => 'Low-profile heatsink, DDR4-3600 for compact builds.',
                    'weight' => 200,
                    'length' => 13,
                    'width' => 1,
                    'height' => 4,
                ],
                [
                    'name' => 'HyperX Fury 8GB DDR4',
                    'sku' => 'RAM-HX-08',
                    'price' => 685000,
                    'stock' => 24,
                    'description' => 'Entry-level DDR4-3200 memory for everyday use.',
                    'weight' => 170,
                    'length' => 13,
                    'width' => 1,
                    'height' => 4,
                ],
            ],
            'cpu' => [
                [
                    'name' => 'Intel Core i5-14600K',
                    'sku' => 'CPU-INT-1460K',
                    'price' => 5350000,
                    'stock' => 7,
                    'description' => '14-core Raptor Lake CPU with unlocked multiplier.',
                    'weight' => 250,
                    'length' => 5,
                    'width' => 5,
                    'height' => 1,
                ],
                [
                    'name' => 'AMD Ryzen 7 7800X3D',
                    'sku' => 'CPU-AMD-7800X3D',
                    'price' => 6999000,
                    'stock' => 5,
                    'description' => '3D V-Cache gaming powerhouse with 8 cores.',
                    'weight' => 240,
                    'length' => 5,
                    'width' => 5,
                    'height' => 1,
                    'discount_percent' => 5,
                ],
            ],
            'gpu' => [
                [
                    'name' => 'NVIDIA GeForce RTX 4070 SUPER',
                    'sku' => 'GPU-NV-4070S',
                    'price' => 11999000,
                    'stock' => 4,
                    'description' => 'Ada Lovelace GPU with DLSS 3 for 1440p gaming.',
                    'weight' => 1200,
                    'length' => 32,
                    'width' => 5,
                    'height' => 14,
                ],
                [
                    'name' => 'MSI Radeon RX 7800 XT Gaming Trio',
                    'sku' => 'GPU-AMD-7800XT',
                    'price' => 10499000,
                    'stock' => 3,
                    'description' => 'Custom triple-fan cooler with RGB lighting.',
                    'weight' => 1250,
                    'length' => 33,
                    'width' => 5,
                    'height' => 14,
                ],
            ],
            'gaming' => [
                [
                    'name' => 'Lynx Gaming Strike X1',
                    'sku' => 'PB-LYNX-X1',
                    'price' => 15999000,
                    'stock' => 5,
                    'description' => 'Prebuilt gaming PC untuk 1080p–1440p gaming. Ditenagai Intel Core i5-13400F, NVIDIA GeForce RTX 3060, 16GB DDR4 3200MHz, storage 512GB NVMe SSD, PSU 550W 80+ Bronze, dan cooling air-cooling, cocok untuk gaming kompetitif dan penggunaan harian.',
                    'weight' => 9000,
                    'length' => 45,
                    'width' => 20,
                    'height' => 45,
                ],
                [
                    'name' => 'Aegis Gaming G7 Ryzen Edition',
                    'sku' => 'PB-AEGIS-G7R',
                    'price' => 19999000,
                    'stock' => 3,
                    'description' => 'Prebuilt powerful untuk 1440p–4K gaming. Menggunakan AMD Ryzen 5 7600, GPU Radeon RX 6800 XT, RAM 32GB DDR5 5200MHz, SSD 1TB NVMe, PSU 650W 80+ Gold, dan cooling 240mm liquid cooling. Performa optimal untuk game AAA modern.',
                    'weight' => 10500,
                    'length' => 46,
                    'width' => 22,
                    'height' => 46,
                ],
                [
                    'name' => 'Vortex Ultra X RTX 4070 Super',
                    'sku' => 'PB-VORTEX-U4070S',
                    'price' => 24999000,
                    'stock' => 2,
                    'description' => 'High-end prebuilt gaming dengan performa tinggi untuk high FPS 1440p gaming. Dibekali Intel Core i7-13700KF, RTX 4070 SUPER, RAM 32GB DDR5 6000MHz, storage 1TB NVMe Gen4 SSD, PSU 750W 80+ Gold, dan cooling 360mm liquid cooling.',
                    'weight' => 11000,
                    'length' => 48,
                    'width' => 23,
                    'height' => 48,
                ],
            ],
            'motherboard' => [
                [
                    'name' => 'ASUS ROG Strix B760-F Gaming WiFi',
                    'sku' => 'MB-ASUS-B760F',
                    'price' => 3799000,
                    'stock' => 6,
                    'description' => 'Motherboard LGA1700 untuk Intel Gen 12–14, mendukung DDR5 dan WiFi 6E, cocok untuk gaming build mid–high end.',
                    'weight' => 1200,
                    'length' => 30,
                    'width' => 25,
                    'height' => 6,
                ],
                [
                    'name' => 'MSI B550 Tomahawk MAX WiFi',
                    'sku' => 'MB-MSI-B550TW',
                    'price' => 2899000,
                    'stock' => 8,
                    'description' => 'Motherboard AM4 populer dengan VRM kuat, cocok untuk Ryzen seri 3000–5000. Hadir dengan WiFi 6.',
                    'weight' => 1150,
                    'length' => 30,
                    'width' => 25,
                    'height' => 6,
                ],
            ],
            'power-supply' => [
                [
                    'name' => 'Corsair RM650 80+ Gold Full Modular',
                    'sku' => 'PSU-COR-RM650',
                    'price' => 1549000,
                    'stock' => 10,
                    'description' => 'PSU 650W 80+ Gold full modular, cocok untuk build menengah hingga high-end.',
                    'weight' => 2000,
                    'length' => 16,
                    'width' => 15,
                    'height' => 8,
                ],
                [
                    'name' => 'FSP HV PRO 550W 80+',
                    'sku' => 'PSU-FSP-550',
                    'price' => 585000,
                    'stock' => 15,
                    'description' => 'Power supply 550W bersertifikasi 80+, ideal untuk PC entry-level dan mid-range.',
                    'weight' => 1800,
                    'length' => 16,
                    'width' => 15,
                    'height' => 8,
                ],
            ],
            'hard-drive' => [
                [
                    'name' => 'Seagate Barracuda 1TB 7200RPM',
                    'sku' => 'HDD-SEA-1TB',
                    'price' => 685000,
                    'stock' => 18,
                    'description' => 'HDD 1TB 7200RPM yang cocok untuk storage tambahan atau PC kantor.',
                    'weight' => 400,
                    'length' => 15,
                    'width' => 10,
                    'height' => 2,
                ],
                [
                    'name' => 'WD Blue 2TB 5400RPM',
                    'sku' => 'HDD-WD-2TB',
                    'price' => 945000,
                    'stock' => 12,
                    'description' => 'HDD 2TB dengan reliabilitas tinggi, cocok untuk penggunaan jangka panjang.',
                    'weight' => 450,
                    'length' => 15,
                    'width' => 10,
                    'height' => 2,
                ],
            ],
            'solid-state-drive' => [
                [
                    'name' => 'Samsung 980 NVMe 500GB',
                    'sku' => 'SSD-SAM-980-500',
                    'price' => 789000,
                    'stock' => 20,
                    'description' => 'SSD NVMe cepat dengan kecepatan baca hingga 3500MB/s, cocok untuk OS dan aplikasi.',
                    'weight' => 80,
                    'length' => 8,
                    'width' => 2,
                    'height' => 1,
                ],
                [
                    'name' => 'Kingston A400 480GB',
                    'sku' => 'SSD-KNG-A400-480',
                    'price' => 499000,
                    'stock' => 25,
                    'description' => 'SSD SATA 480GB untuk upgrade PC/laptop dengan harga terjangkau.',
                    'weight' => 70,
                    'length' => 10,
                    'width' => 7,
                    'height' => 1,
                ],
            ],
            'casing' => [
                [
                    'name' => 'NZXT H510 Flow',
                    'sku' => 'CASE-NZXT-H510F',
                    'price' => 1199000,
                    'stock' => 7,
                    'description' => 'Mid-tower airflow tinggi dengan desain minimalis dan kaca tempered.',
                    'weight' => 6000,
                    'length' => 40,
                    'width' => 21,
                    'height' => 46,
                ],
                [
                    'name' => 'Lian Li Lancool 215',
                    'sku' => 'CASE-LIAN-215',
                    'price' => 1049000,
                    'stock' => 9,
                    'description' => 'Case airflow terbaik dengan dual 200mm RGB fan.',
                    'weight' => 6500,
                    'length' => 42,
                    'width' => 23,
                    'height' => 47,
                ],
            ],
            'office' => [
                [
                    'name' => 'Office PC Basic i3',
                    'sku' => 'OFF-PC-I3',
                    'price' => 5999000,
                    'stock' => 5,
                    'description' => 'PC kantor dengan Intel Core i3, 8GB RAM, 256GB SSD. Cocok untuk administrasi dan aplikasi ringan.',
                    'weight' => 7000,
                    'length' => 40,
                    'width' => 20,
                    'height' => 40,
                ],
                [
                    'name' => 'Office PC Ryzen 3',
                    'sku' => 'OFF-PC-R3',
                    'price' => 6499000,
                    'stock' => 4,
                    'description' => 'PC kantor hemat daya menggunakan Ryzen 3, 8GB RAM, 512GB SSD. Ideal untuk produktivitas harian.',
                    'weight' => 7200,
                    'length' => 40,
                    'width' => 20,
                    'height' => 40,
                ],
            ],
            'school' => [
                [
                    'name' => 'School PC Intel Basic',
                    'sku' => 'SCH-PC-INT',
                    'price' => 4499000,
                    'stock' => 6,
                    'description' => 'PC sekolah untuk e-learning dan browsing. Intel Pentium Gold, 8GB RAM, 256GB SSD.',
                    'weight' => 6500,
                    'length' => 38,
                    'width' => 19,
                    'height' => 38,
                ],
                [
                    'name' => 'School PC Ryzen Basic',
                    'sku' => 'SCH-PC-RYZ',
                    'price' => 4699000,
                    'stock' => 6,
                    'description' => 'PC sekolah berbasis AMD Ryzen 3, ideal untuk belajar online, aplikasi ringan, dan multitasking dasar.',
                    'weight' => 6600,
                    'length' => 38,
                    'width' => 19,
                    'height' => 38,
                ],
            ],


        ];

        foreach ($catalog as $categorySlug => $products) {
            $category = Category::query()->firstOrCreate(
                ['slug' => $categorySlug],
                [
                    'name' => Str::upper(str_replace('-', ' ', $categorySlug)),
                    'description' => 'Kategori contoh untuk '.$categorySlug,
                    'is_active' => true,
                ]
            );

            foreach ($products as $productData) {
                $slug = Str::slug(Arr::get($productData, 'slug', Arr::get($productData, 'name')));
                $hasDiscount = array_key_exists('discount_percent', $productData) || array_key_exists('discount_price', $productData);

                Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'brand_id' => null,
                        'name' => $productData['name'],
                        'sku' => strtoupper($productData['sku']),
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'discount_percent' => $productData['discount_percent'] ?? null,
                        'discount_price' => $productData['discount_price'] ?? null,
                        'discount_start' => $hasDiscount ? now()->subDays(2) : null,
                        'discount_end' => $hasDiscount ? now()->addDays(5) : null,
                        'stock' => $productData['stock'],
                        'weight' => $productData['weight'],
                        'height' => $productData['height'],
                        'length' => $productData['length'],
                        'width' => $productData['width'],
                        'product_image' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}