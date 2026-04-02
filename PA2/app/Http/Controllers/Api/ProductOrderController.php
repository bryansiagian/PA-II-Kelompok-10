<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderNotification;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Product;
use App\Models\ProductOrderStatus;
use App\Models\ProductOrderDelivery;
use App\Models\ProductOrderType; // Import Model Tipe Pesanan (Kendaraan)
use App\Models\Cart;
use App\Models\AuditLog;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductOrderController extends Controller {

    /**
     * Mengambil daftar pesanan.
     */
    public function index() {
        try {
            $user = auth()->user();
            // Eager load status, type (kendaraan), delivery method, dan detail produk
            $query = ProductOrder::with([
                'status',
                'type', // Menampilkan info kendaraan (Motor/Mobil)
                'items.product.warehouse',
                'user',
                'delivery.status'
            ])->latest();

            if ($user->hasRole('customer')) {
                $query->where('user_id', $user->id);
            }

            return response()->json($query->get(), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Checkout Keranjang (Store Order).
     */
    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            $userId = auth()->id();
            // Load cart dengan produk untuk hitung total
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            if($cartItems->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong'], 422);
            }

            // --- HITUNG LOGISTIK & TOTAL ---
            $totalQuantity = 0;
            $subTotal = 0;
            $limitMotor = 50; // Ambang batas kuantitas untuk pindah ke mobil

            foreach($cartItems as $item) {
                $totalQuantity += (int)$item->quantity;
                $subTotal += ($item->product->price * $item->quantity);
            }

            /**
             * LOGIKA PENENTUAN KENDARAAN (Product Order Type)
             * ID 1: Motorcycle (Rutin/Kecil)
             * ID 2: Car/Van (Bulky/Besar)
             */
            $typeId = ($totalQuantity > $limitMotor) ? 2 : 1;

            // Ambil ID Status 'Pending'
            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();

            // Ambil ID Metode Pengiriman (Delivery/Self Pickup)
            $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
            $deliveryMethod = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

            // --- SIMPAN HEADER ORDER ---
            $order = ProductOrder::create([
                'user_id'                    => $userId,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => $typeId, // Mengisi tipe kendaraan berdasarkan ID tabel lookup
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'notes'                      => $request->notes,
                'total'                      => $subTotal
            ]);

            // --- SIMPAN DETAIL ORDER (HISTORICAL PRICING) ---
            foreach ($cartItems as $item) {
                ProductOrderDetail::create([
                    'product_order_id' => $order->id, // UUID
                    'product_id'       => $item->product_id,
                    'quantity'         => $item->quantity,
                    'price_at_order'   => $item->product->price, // Simpan harga saat transaksi terjadi
                ]);
            }

            // Kosongkan Keranjang
            Cart::where('user_id', $userId)->delete();

            AuditLog::create([
                'user_id' => $userId,
                'action'  => "CREATE ORDER: Membuat pesanan #{$order->id} (Kendaraan ID: {$typeId})"
            ]);

            return response()->json(['message' => 'Pesanan berhasil dibuat!', 'order_id' => $order->id], 201);
        });
    }

    public function quickStore(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'notes' => 'nullable|string'
        ]);

        return DB::transaction(function() use ($request) {
            $userId = auth()->id();
            $product = Product::findOrFail($request->product_id);

            // 1. Cek Stok
            if ($product->stock < 1) {
                return response()->json(['message' => 'Stok produk habis'], 422);
            }

            // 2. Tentukan Kendaraan (1 item pasti Motorcycle)
            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();

            // Default untuk pesanan langsung: Delivery & Routine
            $deliveryMethod = ProductOrderDelivery::where('name', 'Delivery')->first();

            // 3. Simpan Header Order
            $order = ProductOrder::create([
                'user_id'                    => $userId,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => 1, // ID 1 = Motorcycle
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'notes'                      => $request->notes ?? 'Pesanan Instan',
                'total'                      => $product->price
            ]);

            // 4. Simpan Detail Order
            ProductOrderDetail::create([
                'product_order_id' => $order->id,
                'product_id'       => $product->id,
                'quantity'         => 1,
                'price_at_order'   => $product->price,
            ]);

            AuditLog::create([
                'user_id' => $userId,
                'action'  => "QUICK ORDER: Pesanan instan #{$order->id} (Produk: {$product->name})"
            ]);

            return response()->json([
                'message' => 'Pesanan instan berhasil dibuat!',
                'order_id' => $order->id
            ], 201);
        });
    }

    /**
     * Menyetujui Pesanan & Potong Stok.
     */
    public function approve($id) {
        return DB::transaction(function() use ($id) {
            $order = ProductOrder::with(['items.product', 'user'])->lockForUpdate()->findOrFail($id);

            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();
            if ($order->product_order_status_id !== $statusPending->id) {
                return response()->json(['message' => 'Pesanan sudah diproses sebelumnya'], 422);
            }

            foreach($order->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if($product->stock < $item->quantity) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }

                // Potong Stok Fisik
                $product->decrement('stock', $item->quantity);

                // Catat di Stock Log
                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
                    'type'       => 'out',
                    'quantity'   => $item->quantity,
                    'reference'  => 'Request'
                ]);
            }

            // Update Status ke Processed (Disetujui/Sedang Disiapkan)
            $statusApproved = ProductOrderStatus::where('name', 'Processed')->first();
            $order->update(['product_order_status_id' => $statusApproved->id]);

            // Notifikasi Email
            try {
                Mail::to($order->user->email)->send(new OrderNotification($order, 'Disetujui'));
            } catch (\Exception $e) {
                Log::error("Mail Error: " . $e->getMessage());
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE ORDER: Menyetujui pesanan #{$id}"]);

            return response()->json(['message' => 'Pesanan berhasil disetujui dan stok telah dipotong']);
        });
    }

    /**
     * Selesaikan Pesanan Tipe Ambil Sendiri (Self Pickup).
     */
    public function completePickup($id) {
        try {
            return DB::transaction(function() use ($id) {
                $order = ProductOrder::with(['user', 'status'])->findOrFail($id);
                $deliveryMethod = ProductOrderDelivery::where('name', 'Self Pickup')->first();

                if ($order->product_order_delivery_id !== $deliveryMethod->id) {
                    return response()->json(['message' => 'Metode pesanan ini bukan Ambil Sendiri'], 422);
                }

                $statusApproved = ProductOrderStatus::where('name', 'Processed')->first();
                if ($order->product_order_status_id !== $statusApproved->id) {
                    return response()->json(['message' => 'Pesanan harus berstatus Processed'], 422);
                }

                $statusCompleted = ProductOrderStatus::where('name', 'Completed')->first();
                $order->update(['product_order_status_id' => $statusCompleted->id]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "PICKUP COMPLETE: Pesanan #{$id} telah diambil oleh {$order->user->name}"
                ]);

                return response()->json(['message' => 'Konfirmasi pengambilan berhasil!']);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tolak Pesanan.
     */
    public function reject($id) {
        $statusRejected = ProductOrderStatus::where('name', 'Rejected')->first();
        $order = ProductOrder::findOrFail($id);
        $order->update(['product_order_status_id' => $statusRejected->id]);

        AuditLog::create(['user_id' => auth()->id(), 'action' => "REJECT ORDER: Menolak pesanan #{$id}"]);
        return response()->json(['message' => 'Pesanan telah ditolak']);
    }

    /**
     * Batalkan Pesanan oleh Customer.
     */
    public function cancel($id) {
        return DB::transaction(function() use ($id) {
            $order = ProductOrder::with(['items', 'status'])->lockForUpdate()->findOrFail($id);

            // Cek jika pesanan sudah masuk tahap logistik
            $invalidStatusNames = ['Shipping', 'Completed'];
            $invalidStatusIds = ProductOrderStatus::whereIn('name', $invalidStatusNames)->pluck('id')->toArray();

            if (in_array($order->product_order_status_id, $invalidStatusIds)) {
                return response()->json(['message' => 'Pesanan sudah dalam pengiriman dan tidak bisa dibatalkan'], 422);
            }

            // Jika status sudah Approved/Processed (stok sudah terpotong), kembalikan stok
            $statusApproved = ProductOrderStatus::where('name', 'Processed')->first();
            if ($order->product_order_status_id === $statusApproved->id) {
                foreach ($order->items as $item) {
                    Product::find($item->product_id)->increment('stock', $item->quantity);

                    StockLog::create([
                        'product_id' => $item->product_id,
                        'user_id'    => auth()->id(),
                        'type'       => 'in',
                        'quantity'   => $item->quantity,
                        'reference'  => 'Manual' // Pengembalian stok
                    ]);
                }
            }

            $statusCancelled = ProductOrderStatus::where('name', 'Cancelled')->first();
            $order->update(['product_order_status_id' => $statusCancelled->id]);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "CANCEL ORDER: Membatalkan pesanan #{$id}"]);
            return response()->json(['message' => 'Pesanan berhasil dibatalkan']);
        });
    }
}
