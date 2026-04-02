<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory; // Gunakan model baru
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    /**
     * Menampilkan semua kategori produk beserta jumlah produk di dalamnya.
     */
    public function index() {
        // Menggunakan withCount('products') sesuai relasi di model baru
        return response()->json(ProductCategory::withCount('products')->where('active', 1)->latest()->get());
    }

    /**
     * Menyimpan kategori produk baru.
     */
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:product_categories,name',
            'code' => 'nullable|string|unique:product_categories,code' // Tambahan field code
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

    public function show($id) {
        return ProductCategory::findOrFail($id);
    }

    /**
     * Memperbarui data kategori.
     */
    public function update(Request $request, $id) {
        $cat = ProductCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:product_categories,name,'.$id,
            'code' => 'nullable|string|unique:product_categories,code,'.$id
        ]);

        return DB::transaction(function() use ($request, $cat) {
            $cat->update($request->all());

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "INVENTORY: Memperbarui kategori produk - {$cat->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diubah']);
        });
    }

    /**
     * Menghapus (mengarsipkan) kategori.
     */
    public function destroy($id) {
        $cat = ProductCategory::findOrFail($id);

        // Proteksi: Jangan hapus jika kategori masih dipakai oleh produk aktif
        if($cat->products()->count() > 0) {
            return response()->json([
                'message' => 'Gagal! Kategori ini masih digunakan oleh beberapa produk aktif.'
            ], 422);
        }

        return DB::transaction(function() use ($cat) {
            // Sesuai standar proyek ini, gunakan sistem arsip (active = 0)
            $cat->update(['active' => 0]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "INVENTORY: Mengarsipkan kategori produk - {$cat->name}"
            ]);

            return response()->json(['message' => 'Kategori berhasil diarsipkan']);
        });
    }
}