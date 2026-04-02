<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product; // Ganti dari Drug
use App\Models\StockLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Menampilkan semua daftar produk beserta kategori dan gudangnya.
     */
    public function index() {
        try {
            // Relasi disesuaikan dengan skema baru: category dan warehouse
            return Product::with('category', 'warehouse')->where('active', 1)->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal di server: ' . $e->getMessage()], 500);
        }
    }

    public function public()
{
    return \App\Models\Product::where('active', 1)
        ->select('id','name','price','stock','unit','image','sku','description','product_category_id')
        ->with('category:id,name')
        ->latest()
        ->get();
}

    /**
     * MENDAFTARKAN PRODUK BARU.
     */
    public function store(Request $request) {
        $request->validate([
            'name'                => 'required|string|max:255',
            'product_code'        => 'nullable|string|max:50',
            'sku'                 => 'required|string|unique:products,sku',
            'product_category_id' => 'required|exists:product_categories,id', // Sesuai tabel baru
            'warehouse_id'        => 'required|exists:warehouses,id',        // Sesuai tabel baru
            'unit'                => 'required|string',
            'price'               => 'required|numeric|min:0',               // Field baru
            'min_stock'           => 'required|integer|min:0',
            'stock'               => 'required|integer|min:0',
            'description'         => 'nullable|string',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            return DB::transaction(function() use ($request) {
                $path = null;
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('products', 'public'); // Folder ganti ke products
                }

                // 1. Buat Record Produk (ID UUID dihandle otomatis oleh boot model)
                $product = Product::create([
                    'name'                => $request->name,
                    'product_code'        => $request->product_code,
                    'sku'                 => $request->sku,
                    'product_category_id' => $request->product_category_id,
                    'warehouse_id'        => $request->warehouse_id,
                    'price'               => $request->price,
                    'unit'                => $request->unit,
                    'min_stock'           => $request->min_stock,
                    'stock'               => $request->stock,
                    'description'         => $request->description,
                    'image'               => $path ? 'storage/'.$path : null,
                ]);

                // 2. Jika ada stok awal, catat di StockLog
                if ($request->stock > 0) {
                    StockLog::create([
                        'product_id' => $product->id, // Menggunakan UUID
                        'user_id'    => auth()->id(),
                        'type'       => 'in',
                        'quantity'   => $request->stock,
                        'reference'  => 'Manual' // Sesuai ENUM baru di DBML
                    ]);
                }

                // 3. Catat Audit Log
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "CREATE PRODUCT: Mendaftarkan produk baru {$product->name} (SKU: {$product->sku})"
                ]);

                return response()->json(['message' => 'Produk baru berhasil ditambahkan', 'data' => $product], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambah produk: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE DATA PRODUK.
     */
    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'                => 'required|string',
            'sku'                 => 'required|unique:products,sku,'.$id,
            'product_category_id' => 'required|exists:product_categories,id',
            'warehouse_id'        => 'required|exists:warehouses,id',
            'price'               => 'required|numeric|min:0',
            'unit'                => 'required|string',
            'min_stock'           => 'required|integer',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $data = $request->except(['image', 'stock']);

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
                }
                $path = $request->file('image')->store('products', 'public');
                $data['image'] = 'storage/'.$path;
            }

            $product->update($data);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "UPDATE PRODUCT: Mengubah informasi produk {$product->name}"
            ]);

            return response()->json(['message' => 'Data produk berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * UPDATE STOK (Stock-In Manual).
     */
    public function updateStock(Request $request) {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function() use ($request) {
                $product = Product::findOrFail($request->product_id);
                $product->increment('stock', $request->quantity);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
                    'type'       => 'in',
                    'quantity'   => $request->quantity,
                    'reference'  => 'Manual'
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "STOCK-IN: Menambah {$request->quantity} unit produk {$product->name}"
                ]);

                return response()->json(['message' => 'Stok ' . $product->name . ' Berhasil Diperbarui']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id) {
        return Product::with('category', 'warehouse')->findOrFail($id);
    }

    public function destroy($id) {
        try {
            $product = Product::findOrFail($id);
            $name = $product->name;

            if ($product->image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $product->image));
            }

            $product->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "DELETE PRODUCT: Menghapus produk {$name} dari sistem"
            ]);

            return response()->json(['message' => 'Produk berhasil dihapus dari sistem']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}
