<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
    [
        'role_id' => 1,
        'name' => 'Admin Barang',
        'email' => 'barang@gtech.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'role_id' => 3,
        'name' => 'Admin Pengiriman',
        'email' => 'pengiriman@gtech.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'role_id' => 4,
        'name' => 'Admin Keuangan',
        'email' => 'keuangan@gtech.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);

    }
}
