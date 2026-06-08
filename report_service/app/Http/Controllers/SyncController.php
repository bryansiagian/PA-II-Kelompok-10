<?php

namespace App\Http\Controllers;

use App\Models\OrderSnapshot;
use App\Models\OrderItemSnapshot;
use App\Models\UserSnapshot;
use App\Models\ProductSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    /**
     * Sync order beserta item-itemnya.
     * Dipanggil saat order dibuat, dibayar, atau berganti status.
     */
    public function syncOrder(Request $request)
    {
        $this->verifySecret($request);

        $data = $request->validate([
            'id'             => 'required|string',
            'user_id'        => 'required|integer',
            'status_name'    => 'required|string',
            'payment_status' => 'required|string',
            'payment_method' => 'required|string',
            'total'          => 'required|numeric',
            'regency'        => 'nullable|string',
            'district'       => 'nullable|string',
            'village'        => 'nullable|string',
            'paid_at'        => 'nullable|string',
            'created_at'     => 'required|string',
            'items'          => 'sometimes|array',
            'items.*.id'              => 'required|string',
            'items.*.product_name'    => 'required|string',
            'items.*.product_id'      => 'nullable|string',
            'items.*.quantity'        => 'required|integer',
            'items.*.price_at_order'  => 'required|numeric',
        ]);

        DB::transaction(function () use ($data) {
            // Upsert order snapshot
            OrderSnapshot::updateOrCreate(
                ['id' => $data['id']],
                [
                    'user_id'        => $data['user_id'],
                    'status_name'    => $data['status_name'],
                    'payment_status' => $data['payment_status'],
                    'payment_method' => $data['payment_method'],
                    'total'          => $data['total'],
                    'regency'        => $data['regency'] ?? null,
                    'district'       => $data['district'] ?? null,
                    'village'        => $data['village'] ?? null,
                    'paid_at'        => $data['paid_at'] ?? null,
                    'created_at'     => $data['created_at'],
                    'synced_at'      => now(),
                ]
            );

            // Sync items jika dikirim (biasanya hanya saat order pertama kali dibuat)
            if (!empty($data['items'])) {
                // Hapus items lama lalu insert ulang
                OrderItemSnapshot::where('order_id', $data['id'])->delete();

                foreach ($data['items'] as $item) {
                    OrderItemSnapshot::create([
                        'id'             => $item['id'],
                        'order_id'       => $data['id'],
                        'product_name'   => $item['product_name'],
                        'product_id'     => $item['product_id'] ?? null,
                        'quantity'       => $item['quantity'],
                        'price_at_order' => $item['price_at_order'],
                    ]);
                }
            }
        });

        Log::info('Sync order berhasil', ['order_id' => $data['id'], 'status' => $data['status_name']]);

        return response()->json(['message' => 'Order synced']);
    }

    /**
     * Sync data user.
     * Dipanggil saat user diapprove, dibuat, atau diupdate.
     */
    public function syncUser(Request $request)
    {
        $this->verifySecret($request);

        $data = $request->validate([
            'id'                => 'required|integer',
            'name'              => 'required|string',
            'email'             => 'required|email',
            'status'            => 'required|integer',
            'active'            => 'required|integer',
            'regency'           => 'nullable|string',
            'district'          => 'nullable|string',
            'village'           => 'nullable|string',
            'email_verified_at' => 'nullable|string',
            'created_at'        => 'required|string',
        ]);

        UserSnapshot::updateOrCreate(
            ['id' => $data['id']],
            [
                'name'              => $data['name'],
                'email'             => $data['email'],
                'status'            => $data['status'],
                'active'            => $data['active'],
                'regency'           => $data['regency'] ?? null,
                'district'          => $data['district'] ?? null,
                'village'           => $data['village'] ?? null,
                'email_verified_at' => $data['email_verified_at'] ?? null,
                'created_at'        => $data['created_at'],
                'synced_at'         => now(),
            ]
        );

        Log::info('Sync user berhasil', ['user_id' => $data['id']]);

        return response()->json(['message' => 'User synced']);
    }

    /**
     * Sync data produk.
     * Dipanggil saat produk dibuat, diupdate, atau stok berubah.
     */
    public function syncProduct(Request $request)
    {
        $this->verifySecret($request);

        $data = $request->validate([
            'id'            => 'required|string',
            'product_code'  => 'nullable|string',
            'name'          => 'required|string',
            'category_name' => 'nullable|string',
            'price'         => 'required|numeric',
            'unit'          => 'nullable|string',
            'stock'         => 'required|integer',
            'min_stock'     => 'required|integer',
            'active'        => 'required|integer',
            'created_at'    => 'required|string',
        ]);

        ProductSnapshot::updateOrCreate(
            ['id' => $data['id']],
            [
                'product_code'  => $data['product_code'] ?? null,
                'name'          => $data['name'],
                'category_name' => $data['category_name'] ?? null,
                'price'         => $data['price'],
                'unit'          => $data['unit'] ?? null,
                'stock'         => $data['stock'],
                'min_stock'     => $data['min_stock'],
                'active'        => $data['active'],
                'created_at'    => $data['created_at'],
                'synced_at'     => now(),
            ]
        );

        Log::info('Sync produk berhasil', ['product_id' => $data['id']]);

        return response()->json(['message' => 'Product synced']);
    }

    /**
     * Verifikasi internal secret agar endpoint tidak bisa diakses publik.
     */
    private function verifySecret(Request $request): void
    {
        $secret = $request->header('X-Internal-Secret')
            ?? $request->query('internal_secret');

        if ($secret !== env('INTERNAL_SECRET')) {
            abort(403, 'Unauthorized');
        }
    }
}
