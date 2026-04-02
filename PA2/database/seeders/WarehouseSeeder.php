<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Rack;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Gudang
        $warehouse = Warehouse::create([
            'code' => 'WH-MAIN',
            'name' => 'Gudang Utama Farmasi',
            'location' => 'Lantai 1 Sayap Barat',
            'created_by' => 1 // Pastikan ID User 1 (Admin) sudah ada
        ]);
    }
}