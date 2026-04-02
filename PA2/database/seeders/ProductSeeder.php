<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'id' => Str::uuid(), // 🔥 WAJIB untuk UUID
            'product_category_id' => 1, // pastikan ada di tabel categories
            'warehouse_id' => null,
            'sku' => 'PRD-001',
            'name' => 'Paracetamol',
            'image' => 'paracetamol.jpg',
            'price' => 10000,
            'description' => 'Obat penurun demam',
            'unit' => 'pcs',
            'stock' => 50,
            'min_stock' => 10,
            'active' => 1,
        ]);

        Product::create([
            'id' => Str::uuid(),
            'product_category_id' => 1,
            'warehouse_id' => null,
            'sku' => 'PRD-002',
            'name' => 'Vitamin C',
            'image' => 'vitamin-c.jpg',
            'price' => 15000,
            'description' => 'Suplemen daya tahan tubuh',
            'unit' => 'pcs',
            'stock' => 30,
            'min_stock' => 5,
            'active' => 1,
        ]);

        Product::create([
            'id' => Str::uuid(),
            'product_category_id' => 1,
            'warehouse_id' => null,
            'sku' => 'PRD-003',
            'name' => 'Amoxicillin',
            'image' => 'amoxicillin.jpg',
            'price' => 20000,
            'description' => 'Antibiotik untuk infeksi bakteri',
            'unit' => 'pcs',
            'stock' => 40,
            'min_stock' => 10,
            'active' => 1,
        ]);
    }
}
