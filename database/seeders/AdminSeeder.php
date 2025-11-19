<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'role_id' => 1,
                'name' => 'Admin Barang',
                'email' => 'barang@gtech.com',
            ],
            [
                'role_id' => 3,
                'name' => 'Admin Pengiriman',
                'email' => 'pengiriman@gtech.com',
            ],
            [
                'role_id' => 4,
                'name' => 'Admin Keuangan',
                'email' => 'keuangan@gtech.com',
            ],
        ];

        foreach ($admins as $admin) {
            $user = User::query()->firstOrNew(['email' => $admin['email']]);

            if (! $user->exists) {
                $user->id = (string) Str::ulid();
                $user->email_verified_at = now();
                $user->remember_token = Str::random(10);
            }

            $user->fill([
                'role_id' => $admin['role_id'],
                'name' => $admin['name'],
                'password' => Hash::make('password123'),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'remember_token' => $user->remember_token ?? Str::random(10),
            ])->save();
        }
    }
}
