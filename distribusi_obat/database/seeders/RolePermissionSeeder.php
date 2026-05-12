<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Vehicle;
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

        // 3. Buat User

        // ADMIN
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'              => 'Admin Sistem',
                'password'          => Hash::make('password'),
                'status'            => 1,
                'active'            => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );
        $adminUser->syncRoles(['admin']);

        // OPERATOR
        $opUser = User::updateOrCreate(
            ['email' => 'operator@test.com'],
            [
                'name'              => 'Budi Operator',
                'password'          => Hash::make('password'),
                'status'            => 1,
                'active'            => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );
        $opUser->syncRoles(['operator']);

        // COURIER 1
        $courierUser = User::updateOrCreate(
            ['email' => 'courier@test.com'],
            [
                'name'              => 'Andi Kurir',
                'password'          => Hash::make('password'),
                'status'            => 1,
                'active'            => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );
        $courierUser->syncRoles(['courier']);

        // COURIER 2
        $courierUser2 = User::updateOrCreate(
            ['email' => 'courier2@test.com'],
            [
                'name'              => 'Budi Kurir',
                'password'          => Hash::make('password'),
                'status'            => 1,
                'active'            => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );
        $courierUser2->syncRoles(['courier']);

        // CUSTOMER
        $customerUser = User::updateOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name'              => 'Sultan Klinik',
                'password'          => Hash::make('password'),
                'address'           => 'Jl. Kesehatan No. 1, Jakarta',
                'status'            => 1,
                'active'            => 1,
                'email_verified_at' => Carbon::now(),
            ]
        );
        $customerUser->syncRoles(['customer']);

        // 4. Buat Kendaraan Contoh
        $vehicles = [
            [
                'type'         => 'motorcycle',
                'subtype'      => 'bebek',
                'brand'        => 'Honda',
                'plate_number' => 'B 1234 ABC',
                'color'        => 'Merah',
                'active'       => true,
            ],
            [
                'type'         => 'motorcycle',
                'subtype'      => 'matic',
                'brand'        => 'Yamaha',
                'plate_number' => 'B 5678 DEF',
                'color'        => 'Hitam',
                'active'       => true,
            ],
            [
                'type'         => 'motorcycle',
                'subtype'      => 'sport',
                'brand'        => 'Kawasaki',
                'plate_number' => 'B 9012 GHI',
                'color'        => 'Hijau',
                'active'       => true,
            ],
            [
                'type'         => 'car',
                'subtype'      => 'van',
                'brand'        => 'Daihatsu',
                'plate_number' => 'B 1111 JKL',
                'color'        => 'Putih',
                'active'       => true,
            ],
            [
                'type'         => 'car',
                'subtype'      => 'pickup',
                'brand'        => 'Suzuki',
                'plate_number' => 'B 2222 MNO',
                'color'        => 'Biru',
                'active'       => true,
            ],
            [
                'type'         => 'car',
                'subtype'      => 'sedan',
                'brand'        => 'Toyota',
                'plate_number' => 'B 3333 PQR',
                'color'        => 'Silver',
                'active'       => true,
            ],
        ];

        foreach ($vehicles as $v) {
            Vehicle::updateOrCreate(
                ['plate_number' => $v['plate_number']],
                $v
            );
        }
    }
}
