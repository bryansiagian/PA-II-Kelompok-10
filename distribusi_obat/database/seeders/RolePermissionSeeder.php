<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\CourierDetail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Permission
        $permissions = ['manage users', 'manage inventory', 'create request', 'delivery task', 'view reports'];
        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 2. Buat Role & Assign Permission
        $admin = Role::updateOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $operator = Role::updateOrCreate(['name' => 'operator']);
        $operator->syncPermissions(['manage users', 'manage inventory', 'view reports']);

        $customer = Role::updateOrCreate(['name' => 'customer']);
        $customer->syncPermissions(['create request']);

        $courier = Role::updateOrCreate(['name' => 'courier']);
        $courier->syncPermissions(['delivery task']);

        // 3. Buat User Contoh dengan status Email Terverifikasi

        // ADMIN
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Sistem',
                'password' => Hash::make('password'),
                'status' => 1, // Approved
                'active' => 1,
                'email_verified_at' => Carbon::now(), // <--- PENTING: Agar tidak diminta OTP
            ]
        );
        $adminUser->assignRole('admin');

        // OPERATOR
        $opUser = User::updateOrCreate(
            ['email' => 'operator@test.com'],
            [
                'name' => 'Budi Operator',
                'password' => Hash::make('password'),
                'status' => 1, // Approved
                'active' => 1,
                'email_verified_at' => Carbon::now(), // <--- PENTING
            ]
        );
        $opUser->assignRole('operator');

        // COURIER
        $courierUser = User::updateOrCreate(
            ['email' => 'courier@test.com'],
            [
                'name' => 'Andi Kurir',
                'password' => Hash::make('password'),
                'status' => 1, // Approved
                'active' => 1,
                'email_verified_at' => Carbon::now(), // <--- PENTING
            ]
        );
        $courierUser->assignRole('courier');

        // Tambah Detail Kendaraan Kurir (Gunakan updateOrCreate agar tidak duplikat)
        CourierDetail::updateOrCreate(
            ['user_id' => $courierUser->id],
            [
                'vehicle_type' => 'motorcycle',
                'vehicle_plate' => 'B 1234 ABC'
            ]
        );

        // CUSTOMER (Contoh Akun yang sudah aktif)
        $customerUser = User::updateOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Sultan Klinik',
                'password' => Hash::make('password'),
                'address' => 'Jl. Kesehatan No. 1, Jakarta',
                'status' => 1, // Approved
                'active' => 1,
                'email_verified_at' => Carbon::now(), // <--- PENTING
            ]
        );
        $customerUser->assignRole('customer');
    }
}
