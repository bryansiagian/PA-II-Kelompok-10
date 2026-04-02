<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ProductSeeder::class,
            // CmsSeeder::class,
            // WarehouseSeeder::class, // Induk untuk obat
            // MasterDataSeeder::class, // Obat terakhir
            // SystemStatusSeeder::class
        ]);
    }
}
