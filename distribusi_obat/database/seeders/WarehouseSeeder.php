<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\Rack;
use App\Models\User;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil ID Admin untuk field created_by
        // Jika User menggunakan UUID, kita ambil ID pertama. Jika integer, gunakan 1.
        $adminId = User::first()->id ?? 1;

        // 2. Buat Gudang Utama
        $whMain = Warehouse::create([
            'code' => 'WH-MAIN',
            'name' => 'Gudang Utama Farmasi',
            'location' => 'Lantai 1 Sayap Barat',
            'created_by' => $adminId
        ]);

        // 3. Buat Gudang Cadangan / Dingin (Cold Storage)
        $whCold = Warehouse::create([
            'code' => 'WH-COLD',
            'name' => 'Gudang Cold Storage',
            'location' => 'Lantai 1 Area Belakang',
            'created_by' => $adminId
        ]);

        // 4. Buat Rak untuk Gudang Utama (WH-MAIN)
        $racksMain = ['RAK-A1', 'RAK-A2', 'RAK-B1', 'RAK-B2', 'RAK-C1'];
        foreach ($racksMain as $rackName) {
            Rack::create([
                'warehouse_id' => $whMain->id, // Menghubungkan ke WH-MAIN
                'name'       => $rackName,
                'active'     => true,
                'created_by' => $adminId
            ]);
        }

        // 5. Buat Rak untuk Gudang Cold Storage (WH-COLD)
        $racksCold = ['CHILLER-01', 'CHILLER-02', 'FREEZER-01'];
        foreach ($racksCold as $rackName) {
            Rack::create([
                'warehouse_id' => $whCold->id, // Menghubungkan ke WH-COLD
                'name'       => $rackName,
                'active'     => true,
                'created_by' => $adminId
            ]);
        }
    }
}
