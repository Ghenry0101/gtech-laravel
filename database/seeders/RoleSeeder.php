<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        $roles = [
            [
                'id' => 1,
                'posisi' => 'admin_barang',
                'display_name' => 'Admin Barang',
            ],
            [
                'id' => 2,
                'posisi' => 'user',
                'display_name' => 'User',
            ],
            [
                'id' => 3,
                'posisi' => 'admin_pengiriman',
                'display_name' => 'Admin Pengiriman',
            ],
            [
                'id' => 4,
                'posisi' => 'admin_keuangan',
                'display_name' => 'Admin Keuangan',
            ],
        ];

        Role::query()->upsert(
            collect($roles)->map(static fn (array $role) => [
                'id' => $role['id'],
                'posisi' => $role['posisi'],
                'display_name' => $role['display_name'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ])->all(),
            ['id'],
            ['posisi', 'display_name', 'updated_at']
        );
    }
}
