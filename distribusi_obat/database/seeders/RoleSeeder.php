<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'operator', 'customer', 'courier'];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
