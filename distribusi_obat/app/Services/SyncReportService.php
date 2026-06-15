<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Helper untuk sync data ke report_service.
 *
 * Semua method fire-and-forget:
 * - Tidak throw exception jika report_service mati
 * - Tidak mengganggu flow utama
 * - Log warning jika gagal
 */
class SyncReportService
{
    private string $baseUrl;
    private string $secret;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.report_service.url', 'http://127.0.0.1:8002'), '/');
        $this->secret  = config('services.report_service.secret', '');
        $this->timeout = 5; // detik — cukup untuk fire-and-forget
    }

    /**
     * Sync order ke report_service.
     * Panggil setiap kali order dibuat, dibayar, atau berganti status.
     */
    public function syncOrder(\App\Models\ProductOrder $order): void
    {
        try {
            $order->loadMissing(['status', 'items.product']);

            $payload = [
                'id'             => $order->id,
                'user_id'        => $order->user_id,
                'status_name'    => $order->status->name ?? 'Unknown',
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total'          => $order->total,
                'regency'        => $order->regency,
                'district'       => $order->district,
                'village'        => $order->village,
                'phone_order'    => $order->phone_order,   // ← tambah
                'paid_at'        => $order->paid_at?->toISOString(),
                'created_at'     => $order->created_at->toISOString(),
                'items'          => $order->items->map(fn($item) => [
                    'id'             => $item->id,
                    'product_name'   => $item->product->name ?? 'Produk Dihapus',
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'price_at_order' => $item->price_at_order,
                ])->toArray(),
            ];

            $this->post('/api/internal/sync/order', $payload);

        } catch (\Exception $e) {
            Log::warning('[SyncReport] Gagal sync order: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Sync hanya status order (tanpa items).
     * Lebih efisien untuk update status/payment.
     */
    public function syncOrderStatus(\App\Models\ProductOrder $order): void
    {
        try {
            $order->loadMissing('status');

            $this->post('/api/internal/sync/order', [
                'id'             => $order->id,
                'user_id'        => $order->user_id,
                'status_name'    => $order->status->name ?? 'Unknown',
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total'          => $order->total,
                'regency'        => $order->regency,
                'district'       => $order->district,
                'village'        => $order->village,
                'phone_order'    => $order->phone_order,   // ← tambah
                'paid_at'        => $order->paid_at?->toISOString(),
                'created_at'     => $order->created_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::warning('[SyncReport] Gagal sync status order: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Sync user ke report_service.
     * Panggil saat user diapprove atau data user diupdate.
     */
    public function syncUser(\App\Models\User $user): void
    {
        try {
            $this->post('/api/internal/sync/user', [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'phone'             => $user->phone,
                'status'            => $user->status ?? 0,
                'active'                => $user->active ?? 1,   // ← fallback
                'regency'           => $user->regency,
                'district'          => $user->district,
                'village'           => $user->village,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at'        => $user->created_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::warning('[SyncReport] Gagal sync user: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Sync produk ke report_service.
     * Panggil saat produk dibuat, diupdate, atau stok berubah.
     */
    public function syncProduct(\App\Models\Product $product): void
    {
        try {
            $product->loadMissing('category');

            $this->post('/api/internal/sync/product', [
                'id'            => $product->id,
                'product_code'  => $product->product_code,
                'name'          => $product->name,
                'category_name' => $product->category->name ?? null,
                'price'         => $product->price,
                'unit'          => $product->unit,
                'stock'         => $product->stock,
                'min_stock'     => $product->min_stock,
                'active'        => $product->active,
                'created_at'    => $product->created_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::warning('[SyncReport] Gagal sync produk: ' . $e->getMessage(), [
                'product_id' => $product->id,
            ]);
        }
    }

    /**
     * Kirim POST request ke report_service.
     */
    private function post(string $path, array $data): void
    {
        Http::timeout($this->timeout)
            ->withHeaders(['X-Internal-Secret' => $this->secret])
            ->post($this->baseUrl . $path, $data);
    }
}
