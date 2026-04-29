<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory; // Pastikan model ini sudah ada
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori beserta jumlah produk di dalamnya.
     */
    public function index() {
        try {
            // Menggunakan withCount('products') agar sinkron dengan tabel products
            return response()->json(ProductCategory::withCount('products')->where('active', 1)->latest()->get());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat kategori: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|unique:product_categories,name',
            'code' => 'nullable|string|unique:product_categories,code'
        ]);

        return DB::transaction(function() use ($request) {
            $category = ProductCategory::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'active' => 1,
                'created_by' => auth()->id()
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "INVENTORY: Menambah kategori produk baru - {$category->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil ditambahkan', 'data' => $category]);
        });
    }

    /**
     * Tampilkan detail satu kategori.
     */
    public function show($id) {
        try {
            return ProductCategory::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }
    }

    /**
     * Update data kategori.
     */
    public function update(Request $request, $id) {
        $cat = ProductCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:product_categories,name,'.$id,
            'code' => 'nullable|string|unique:product_categories,code,'.$id
        ]);

        return DB::transaction(function() use ($request, $cat) {
            $cat->update($request->all());

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "INVENTORY: Memperbarui kategori produk - {$cat->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diperbarui']);
        });
    }

    /**
     * Hapus (Arsipkan) kategori.
     */
    public function destroy($id) {
        $cat = ProductCategory::findOrFail($id);

        // Proteksi: Cek apakah masih ada produk yang menggunakan kategori ini
        if($cat->products()->count() > 0) {
            return response()->json([
                'message' => 'Gagal! Kategori ini masih digunakan oleh beberapa produk aktif.'
            ], 422);
        }

        return DB::transaction(function() use ($cat) {
            // Sesuai standar proyek, gunakan active = 0 alih-alih hapus permanen
            $cat->update(['active' => 0]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "INVENTORY: Mengarsipkan kategori produk - {$cat->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diarsipkan']);
        });
    }
}
