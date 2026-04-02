<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse; // Ganti dari Storage
use App\Models\Rack;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    // === WAREHOUSE CRUD (Pengganti Storage) ===
    public function indexWarehouses() {
        try {
            return response()->json(Warehouse::where('active', 1)->latest()->get());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat data gudang'], 500);
        }
    }

    public function storeWarehouse(Request $request) {
        $request->validate([
            'code'     => 'required|unique:warehouses,code', // Tambahan kolom code dari DBML
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string'
        ]);

        $warehouse = Warehouse::create([
            'code'     => strtoupper($request->code),
            'name'     => $request->name,
            'location' => $request->location,
            'active'   => 1
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "WAREHOUSE: Menambah gudang baru - {$warehouse->name} ({$warehouse->code})"
        ]);

        return response()->json(['message' => 'Gudang berhasil ditambahkan', 'data' => $warehouse]);
    }

    public function updateWarehouse(Request $request, $id) {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:warehouses,code,'.$id,
            'name' => 'required|string|max:255'
        ]);

        $warehouse->update($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "WAREHOUSE: Memperbarui data gudang {$warehouse->name}"
        ]);

        return response()->json(['message' => 'Data gudang diperbarui']);
    }

    public function destroyWarehouse($id) {
        $warehouse = Warehouse::findOrFail($id);

        // Proteksi: Cek apakah ada produk yang masih terhubung ke gudang ini
        if($warehouse->products()->count() > 0) {
            return response()->json(['message' => 'Gagal! Gudang masih digunakan oleh beberapa produk aktif.'], 422);
        }

        // Jika tabel racks masih ada relasinya
        if(method_exists($warehouse, 'racks') && $warehouse->racks()->count() > 0) {
            return response()->json(['message' => 'Gagal! Gudang masih memiliki data rak.'], 422);
        }

        $warehouse->update(['active' => 0]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "WAREHOUSE: Mengarsipkan gudang {$warehouse->name}"
        ]);

        return response()->json(['message' => 'Gudang berhasil diarsipkan']);
    }

    // === RACK CRUD (Opsional, menyesuaikan revisi Dosen) ===
    public function indexRacks() {
        // Mengambil rak beserta nama gudang (Warehouse)
        return response()->json(Rack::with('warehouse')->where('active', 1)->latest()->get());
    }

    public function storeRack(Request $request) {
        $request->validate([
            'name'         => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id'
        ]);

        $rack = Rack::create($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "WAREHOUSE: Menambah rak baru {$rack->name} pada gudang ID: {$request->warehouse_id}"
        ]);

        return response()->json(['message' => 'Rak berhasil ditambahkan']);
    }

    public function updateRack(Request $request, $id) {
        $rack = Rack::findOrFail($id);
        $rack->update($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "WAREHOUSE: Memperbarui data rak {$rack->name}"
        ]);

        return response()->json(['message' => 'Data rak diperbarui']);
    }

    public function destroyRack($id) {
        $rack = Rack::findOrFail($id);

        // Proteksi: Cek apakah ada produk di rak ini
        if($rack->products()->count() > 0) {
            return response()->json(['message' => 'Gagal! Rak masih berisi stok produk.'], 422);
        }

        $rack->update(['active' => 0]);
        return response()->json(['message' => 'Rak berhasil diarsipkan']);
    }
}