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
            ['name' => 'Admin Sistem',  'email' => 'admin@test.com'],
            ['name' => 'Budi Operator', 'email' => 'operator@test.com'],
            ['name' => 'Andi Kurir',    'email' => 'courier@test.com'],
            ['name' => 'Sultan Klinik', 'email' => 'customer@test.com'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'password'          => Hash::make('password'),
                    'status'            => 1,
                    'active'            => 1,
                    'email_verified_at' => Carbon::now(),
                ]
            );
        }
    }
}
