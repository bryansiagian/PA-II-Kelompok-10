<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID Role agar tidak hardcoded
        $adminRole = Role::where('name', 'admin')->first()->id;
        $operatorRole = Role::where('name', 'operator')->first()->id;
        $customerRole = Role::where('name', 'customer')->first()->id;
        $courierRole = Role::where('name', 'courier')->first()->id;

        // 1. Akun Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'role_id' => $adminRole,
            'phone' => '081234567890',
            'address' => 'Kantor Pusat Logistik'
        ]);

        // 2. Akun Operator Gudang
        User::create([
            'name' => 'Budi Operator',
            'email' => 'operator@warehouse.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'role_id' => $operatorRole,
            'phone' => '081234567891',
            'address' => 'Gudang Utama Blok A'
        ]);

        // 3. Akun Customer (Unit Kesehatan / RS)
        User::create([
            'name' => 'RSUD Sehat Selalu',
            'email' => 'customer@warehouse.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'role_id' => $customerRole,
            'phone' => '081234567892',
            'address' => 'Jl. Kesehatan No. 123'
        ]);

        // 4. Akun Kurir
        User::create([
            'name' => 'Andi Kurir',
            'email' => 'courier@warehouse.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'role_id' => $courierRole,
            'phone' => '081234567893',
            'address' => 'Pool Kendaraan Logistik'
        ]);
    }
}
