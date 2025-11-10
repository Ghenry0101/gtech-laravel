<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'posisi' => 'admin_barang', 'display_name' => 'Admin Barang', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'posisi' => 'user', 'display_name' => 'User', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'posisi' => 'admin_pengiriman', 'display_name' => 'Admin Pengiriman', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'posisi' => 'admin_keuangan', 'display_name' => 'Admin Keuangan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
