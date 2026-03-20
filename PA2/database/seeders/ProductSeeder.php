<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Paracetamol',
            'description' => 'Obat penurun demam',
            'price' => 10000,
            'stock' => 50,
            'image' => 'paracetamol.jpg'
        ]);

        Product::create([
            'name' => 'Vitamin C',
            'description' => 'Suplemen daya tahan tubuh',
            'price' => 15000,
            'stock' => 30,
            'image' => 'vitamin-c.jpg'
        ]);

        Product::create([
            'name' => 'Amoxicillin',
            'description' => 'Antibiotik untuk infeksi bakteri',
            'price' => 20000,
            'stock' => 40,
            'image' => 'amoxicillin.jpg'
        ]);
    }
}
