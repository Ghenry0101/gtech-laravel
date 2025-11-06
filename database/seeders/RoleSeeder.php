<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'admin_barang', 'display_name' => 'Admin_Barang', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'user',  'display_name' => 'User','created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'admin_pengiriman', 'display_name' => 'Admin_Pengiriman','created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'admin_keuangan', 'display_name' => 'Admin_Keuangan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
