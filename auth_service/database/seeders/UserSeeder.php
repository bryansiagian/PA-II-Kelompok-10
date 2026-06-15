<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin Sistem',  'email' => 'admin@test.com',     'phone' => '081234560001'],
            ['name' => 'Budi Operator', 'email' => 'operator@test.com',  'phone' => '081234560002'],
            ['name' => 'Andi Kurir',    'email' => 'courier@test.com',   'phone' => '081234560003'],
            ['name' => 'Budi Kurir',    'email' => 'courier2@test.com',  'phone' => '081234560004'],
            ['name' => 'Sultan Klinik', 'email' => 'customer@test.com',  'phone' => '081234560005'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'password'          => Hash::make('password'),
                    'phone'             => $u['phone'],
                    'status'            => 1,
                    'active'            => 1,
                    'email_verified_at' => Carbon::now(),
                ]
            );
        }
    }
}
