<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default category icon
    |--------------------------------------------------------------------------
    */
    'default_category_icon' => 'images/prebuilt-icon.png',

    /*
    |--------------------------------------------------------------------------
    | Category specific icons
    |--------------------------------------------------------------------------
    |
    | Map a category slug to an icon path relative to the public directory.
    | The slug is matched exactly, but you may also provide underscored
    | variations (e.g. `pc_office`). Feel free to extend this list.
    |
    */
    'category_icons' => [
        'prebuilt' => 'images/prebuilt-icon.png',
        'all-product' => 'images/prebuilt-icon.png',
        'components' => 'images/mobo-icon.png',
        'component' => 'images/mobo-icon.png',
        'pc-gaming' => 'images/prebuilt-icon.png',
        'gaming' => 'images/prebuilt-icon.png',
        'pc_office' => 'images/officePC-icon.png',
        'pc-office' => 'images/officePC-icon.png',
        'office' => 'images/officePC-icon.png',
        'school' => 'images/schoolPC-icon.png',
        'pc-school' => 'images/schoolPC-icon.png',
        'workstation' => 'images/CPU-icon.png',
        'cpu' => 'images/CPU-icon.png',
        'mother-board' => 'images/mobo-icon.png',
        'power-supply' => 'images/PSU-icon.png',
        'ram' => 'images/RAM-icon.png',
        'hard-drive' => 'images/HDD-icon.png',
        'solid-state-drive' => 'images/SSD-icon.png',
        'gpu' => 'images/GPU-icon.png',
        'casing' => 'images/casing-icon.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category background images (used for hero/grid tiles)
    |--------------------------------------------------------------------------
    */
    'category_backgrounds' => [
        'default' => 'images/PcBan.png',
        'prebuilt' => 'images/PcBan.png',
        'all-product' => 'images/PcBan.png',
        'components' => 'images/bayangan-4.png',
        'pc-gaming' => 'images/PcBan.png',
        'gaming' => 'images/PcBan.png',
        'pc-office' => 'images/PcOffice.png',
        'pc_office' => 'images/PcOffice.png',
        'office' => 'images/PcOffice.png',
        'school' => 'images/PcSchool.webp',
        'pc-school' => 'images/PcSchool.webp',
        'workstation' => 'images/PcG1.png',
        'cpu' => 'images/PcG1.png',
        'mother-board' => 'images/bayangan-4.png',
        'power-supply' => 'images/bayangan-3.png',
        'ram' => 'images/Rams.png',
        'hard-drive' => 'images/bayangan-2.png',
        'solid-state-drive' => 'images/bayangan-1.png',
        'gpu' => 'images/PcBlack.png',
        'casing' => 'images/bayangan-pc.png',
    ],

    'static_categories' => [
        ['slug' => 'cpu', 'name' => 'CPU', 'icon' => 'images/CPU-icon.png'],
        ['slug' => 'mother-board', 'name' => 'MOTHER BOARD', 'icon' => 'images/mobo-icon.png'],
        ['slug' => 'power-supply', 'name' => 'POWER SUPPLY', 'icon' => 'images/PSU-icon.png'],
        ['slug' => 'ram', 'name' => 'RAM', 'icon' => 'images/RAM-icon.png'],
        ['slug' => 'hard-drive', 'name' => 'HARD DRIVE', 'icon' => 'images/HDD-icon.png'],
        ['slug' => 'solid-state-drive', 'name' => 'SOLID STATE DRIVE', 'icon' => 'images/SSD-icon.png'],
        ['slug' => 'gpu', 'name' => 'GRAPHIC CARD', 'icon' => 'images/GPU-icon.png'],
        ['slug' => 'casing', 'name' => 'CASING', 'icon' => 'images/casing-icon.png'],
        ['slug' => 'gaming', 'name' => 'GAMING', 'icon' => 'images/prebuilt-icon.png'],
        ['slug' => 'office', 'name' => 'OFFICE', 'icon' => 'images/officePC-icon.png'],
        ['slug' => 'school', 'name' => 'SCHOOL', 'icon' => 'images/schoolPC-icon.png'],
    ],
];
