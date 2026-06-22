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
        // 1. Master Kategori Produk
        $cat1 = ProductCategory::create(['code' => 'ALG', 'name' => 'Analgetik']);
        $cat2 = ProductCategory::create(['code' => 'ABT', 'name' => 'Antibiotik']);
        $cat3 = ProductCategory::create(['code' => 'PNC', 'name' => 'Antasida / Pencernaan']);
        $cat4 = ProductCategory::create(['code' => 'BTF', 'name' => 'Batuk & Flu']);
        $cat5 = ProductCategory::create(['code' => 'ALR', 'name' => 'Antihistamin / Alergi']);
        $cat6 = ProductCategory::create(['code' => 'VIT', 'name' => 'Suplemen & Vitamin']);
        $cat7 = ProductCategory::create(['code' => 'DAB', 'name' => 'Antidiabetes']);

        // 2. Mengambil Data Gudang Eksisting
        // Jika tidak ditemukan gudang spesifik, fallback ke gudang pertama yang ada di DB
        $whMain = Warehouse::where('code', 'WH-MAIN')->first() ?? Warehouse::first();
        $whCold = Warehouse::where('code', 'WH-COLD')->first() ?? $whMain;

        // --- DATA EKSISTING (PROD-001 & PROD-002) ---
        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-001',
            'product_code' => 'AN-PCT5',
            'name' => 'Paracetamol 500mg',
            'unit' => 'Strip',
            'stock' => 100,
            'price' => 5000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat2->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-002',
            'product_code' => 'AB-AMOX',
            'name' => 'Amoxicillin Box',
            'unit' => 'Box',
            'stock' => 50,
            'price' => 150000,
            'created_by' => 1
        ]);

        // --- DATA OBAT BARU (PROD-003 s.d PROD-012) ---
        Product::create([
            'product_category_id' => $cat3->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-003',
            'product_code' => 'PC-DIAP',
            'name' => 'Diapet Kapsul',
            'unit' => 'Strip',
            'stock' => 150,
            'price' => 7500,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat4->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-004',
            'product_code' => 'BF-OBHC',
            'name' => 'OBH Combi Sirup 100ml',
            'unit' => 'Botol',
            'stock' => 80,
            'price' => 22000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat3->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-005',
            'product_code' => 'PC-OMEP',
            'name' => 'Omeprazole 20mg',
            'unit' => 'Strip',
            'stock' => 200,
            'price' => 12000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat3->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-006',
            'product_code' => 'PC-ANTA',
            'name' => 'Antasida Doen Tablet',
            'unit' => 'Strip',
            'stock' => 300,
            'price' => 3500,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat3->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-007',
            'product_code' => 'PC-PROM',
            'name' => 'Promag Tablet',
            'unit' => 'Strip',
            'stock' => 250,
            'price' => 9000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat2->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-008',
            'product_code' => 'AB-CEFI',
            'name' => 'Cefixime 100mg',
            'unit' => 'Strip',
            'stock' => 100,
            'price' => 35000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat5->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-009',
            'product_code' => 'AL-LORA',
            'name' => 'Loratadine 10mg',
            'unit' => 'Strip',
            'stock' => 120,
            'price' => 8000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat5->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-010',
            'product_code' => 'AL-CTMT',
            'name' => 'CTM Tablet',
            'unit' => 'Strip',
            'stock' => 500,
            'price' => 5000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-011',
            'product_code' => 'AN-ASME',
            'name' => 'Asam Mefenamat 500mg',
            'unit' => 'Strip',
            'stock' => 200,
            'price' => 6500,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-012',
            'product_code' => 'AN-IBUP',
            'name' => 'Ibuprofen 400mg',
            'unit' => 'Strip',
            'stock' => 180,
            'price' => 7000,
            'created_by' => 1
        ]);

        // --- 3 DATA TAMBAHAN PELENGKAP KATALOG ---
        Product::create([
            'product_category_id' => $cat6->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-013',
            'product_code' => 'VIT-VITC',
            'name' => 'Vitamin C 500mg',
            'unit' => 'Strip',
            'stock' => 400,
            'price' => 5000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat7->id,
            'warehouse_id' => $whMain->id,
            'sku' => 'PROD-014',
            'product_code' => 'AD-METF',
            'name' => 'Metformin 500mg',
            'unit' => 'Strip',
            'stock' => 150,
            'price' => 8500,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat7->id,
            'warehouse_id' => $whCold->id, // Disimpan di Gudang Cold Storage
            'sku' => 'PROD-015',
            'product_code' => 'AD-INSG',
            'name' => 'Insulin Glargine Pen',
            'unit' => 'Pcs',
            'stock' => 20,
            'price' => 165000,
            'created_by' => 1
        ]);

        // --- 5 DATA COLD STORAGE EXTRA ---
        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $whCold->id,
            'sku' => 'PROD-016',
            'product_code' => 'OB-OXYT',
            'name' => 'Oxytocin Injection 10 IU/ml',
            'unit' => 'Ampul',
            'stock' => 50,
            'price' => 15000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat6->id,
            'warehouse_id' => $whCold->id,
            'sku' => 'PROD-017',
            'product_code' => 'VK-HEPB',
            'name' => 'Vaksin Hepatitis B Rekombinan Injection',
            'unit' => 'Pcs',
            'stock' => 40,
            'price' => 65000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat1->id,
            'warehouse_id' => $whCold->id,
            'sku' => 'PROD-018',
            'product_code' => 'MT-LATA',
            'name' => 'Latanoprost Eye Drops 0.005%',
            'unit' => 'Botol',
            'stock' => 15,
            'price' => 120000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat6->id,
            'warehouse_id' => $whCold->id,
            'sku' => 'PROD-019',
            'product_code' => 'VK-OPV2',
            'name' => 'Vaksin Polio Oral (OPV) 20 Dosis',
            'unit' => 'Vial',
            'stock' => 10,
            'price' => 180000,
            'created_by' => 1
        ]);

        Product::create([
            'product_category_id' => $cat3->id,
            'warehouse_id' => $whCold->id,
            'sku' => 'PROD-020',
            'product_code' => 'PC-SUPP',
            'name' => 'Dulcolax Suppositoria 10mg',
            'unit' => 'Pcs',
            'stock' => 60,
            'price' => 24000,
            'created_by' => 1
        ]);
    }
}
