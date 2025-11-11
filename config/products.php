<?php

return [
    'items' => [
        [
            'slug'          => 'PC-Gmaing',
            'id'            => 'P-0001',
            'kategori'      => 'prebuilt', 
            'nama_produk'   => 'PC gaming anti ngelag',
            'deskripsi'     => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
Curabitur suscipit, nunc ut pulvinar varius, purus sem ultrices magna, vitae 
fringilla odio quam id arcu.",
            'harga'         => 1299.99,
            'stok'          => 12,
            'weight'        => 6.4,
            'length'        => 49,
            'width'         => 24,
            'height'        => 51,
            'gambar_produk' => 'images/PC.png',
            'status'        => 'available',

            'gallery' => [
                'images/PC.png',
                'images/GPU.png',
                'images/CPU.png',
            ],

            'spesifikasi' => [
                'Chipset'    => 'Z790',
                'Processor'  => 'Intel Core i9',
                'Graphics'   => 'RTX 4080',
                'Memory'     => '32GB DDR5',
                'Storage'    => '1TB NVMe',
                'PSU'        => '750W 80+ Gold',
                'Case'       => 'ATX Tempered Glass',
            ],

            'ulasan' => [
                [
                    'nama'   => 'Adit',
                    'rating' => 5,
                    'isi'    => 'Build rapi, performa kenceng!',
                ],
                [
                    'nama'   => 'Mona',
                    'rating' => 4,
                    'isi'    => 'Desain cakep, kipas cukup senyap.',
                ],
            ],
        ],
    ],
];
