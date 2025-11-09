<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insertOrIgnore([
            [
                'display_name' => 'Admin Barang',
                'email' => 'barang@gtech.com',
                'password' => Hash::make('password123'),
                'position' => 'product_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'display_name' => 'Admin Pengiriman',
                'email' => 'pengiriman@gtech.com',
                'password' => Hash::make('password123'),
                'position' => 'shipping_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'display_name' => 'Admin Keuangan',
                'email' => 'keuangan@gtech.com',
                'password' => Hash::make('password123'),
                'position' => 'finance_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
