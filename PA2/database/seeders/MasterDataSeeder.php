<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $cat1 = ProductCategory::create(['code' => 'ALG', 'name' => 'Analgetik']);
        $cat2 = ProductCategory::create(['code' => 'ABT', 'name' => 'Antibiotik']);

        $wh = Warehouse::first();

        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $wh->id,
            'sku' => 'PROD-001',
            'product_code' => 'PC-PARA',
            'name' => 'Paracetamol 500mg',
            'unit' => 'Strip',
            'stock' => 100,
            'price' => 5000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat2->id,
            'warehouse_id' => $wh->id,
            'sku' => 'PROD-002',
            'product_code' => 'PC-AMOX',
            'name' => 'Amoxicillin Box',
            'unit' => 'Box',
            'stock' => 50,
            'price' => 150000,
            'created_by' => 1
        ]);
    }
}