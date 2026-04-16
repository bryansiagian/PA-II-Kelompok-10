<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderNotification;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Product;
use App\Models\ProductOrderStatus;
use App\Models\ProductOrderDelivery;
use App\Models\ProductOrderType;
use App\Models\Cart;
use App\Models\AuditLog;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductOrderController extends Controller {

    public function index() {
        try {
            $user = auth()->user();
            $query = ProductOrder::with([
                'status',
                'type',
                'items.product.warehouse',
                'user',
                'delivery.status',
                'delivery.courier'
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
     * Proses Checkout Keranjang (Standard Store)
     */
    public function store(Request $request) {
        return DB::transaction(function() use ($request) {
            $userId = auth()->id();
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            if($cartItems->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong'], 422);
            }

            $totalQuantity = 0;
            $subTotal = 0;
            $anyBulky = false;
            $limitMotor = 50;

            foreach($cartItems as $item) {
                $totalQuantity += (int)$item->quantity;
                $subTotal += ($item->product->price * $item->quantity);
                if ($item->product->is_bulky) $anyBulky = true;
            }

            // LOGIKA PENENTUAN KENDARAAN
            $typeId = ($totalQuantity > $limitMotor || $anyBulky) ? 2 : 1;
            // TAMBAHKAN INI: Konversi ID ke string untuk kolom required_vehicle
            $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();
            $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
            $deliveryMethod = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

            $order = ProductOrder::create([
                'user_id'                    => $userId,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => $typeId,
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'required_vehicle'           => $vehicleName, // SEKARANG DISIMPAN KE DATABASE
                'notes'                      => $request->notes,
                'total'                      => $subTotal
            ]);

            foreach ($cartItems as $item) {
                ProductOrderDetail::create([
                    'product_order_id' => $order->id,
                    'product_id'       => $item->product_id,
                    'quantity'         => $item->quantity,
                    'price_at_order'   => $item->product->price,
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            return response()->json(['message' => 'Pesanan berhasil dibuat!', 'order_id' => $order->id], 201);
        });
    }

    /**
     * Proses Pembuatan Pesanan Manual oleh Admin/Operator (Admin Store)
     */
    public function adminStore(Request $request) {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'request_type'=> 'required|in:delivery,self_pickup',
            'courier_id'  => 'nullable|exists:users,id',
        ]);

        return DB::transaction(function() use ($request) {
            $product = Product::findOrFail($request->product_id);

            if($product->stock < $request->quantity) {
                return response()->json(['message' => "Stok tidak mencukupi"], 422);
            }

            // LOGIKA KENDARAAN
            $typeId = ($request->quantity > 50 || $product->is_bulky) ? 2 : 1;
            // TAMBAHKAN INI: Konversi ID ke string untuk kolom required_vehicle
            $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();
            $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
            $deliveryMethod = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

            $order = ProductOrder::create([
                'user_id'                    => $request->customer_id,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => $typeId,
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'required_vehicle'           => $vehicleName, // SEKARANG DISIMPAN KE DATABASE
                'notes'                      => $request->notes ?? 'Dibuat secara manual oleh Admin/Operator',
                'total'                      => $product->price * $request->quantity
            ]);

            ProductOrderDetail::create([
                'product_order_id' => $order->id,
                'product_id'       => $product->id,
                'quantity'         => $request->quantity,
                'price_at_order'   => $product->price,
            ]);

            // Logika Penunjukan Kurir Langsung (Tetap sama)
            if ($request->courier_id && $request->request_type === 'delivery') {
                $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();
                $delivery = Delivery::create([
                    'product_order_id'   => $order->id,
                    'courier_id'         => $request->courier_id,
                    'delivery_status_id' => $claimedStatus->id,
                    'tracking_number'    => 'TRK-' . strtoupper(bin2hex(random_bytes(4)))
                ]);
                $order->update(['product_order_status_id' => ProductOrderStatus::where('name', 'Shipping')->first()->id]);
            }

            return response()->json(['message' => 'Pesanan manual berhasil dibuat!'], 201);
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

            if ($product->stock < 1) {
                return response()->json(['message' => 'Stok produk habis'], 422);
            }

            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();
            $deliveryMethod = ProductOrderDelivery::where('name', 'Delivery')->first();

            $order = ProductOrder::create([
                'user_id'                    => $userId,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => 1,
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'notes'                      => $request->notes ?? 'Pesanan Instan',
                'total'                      => $product->price
            ]);

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

                $product->decrement('stock', $item->quantity);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id'    => auth()->id(),
                    'type'       => 'out',
                    'quantity'   => $item->quantity,
                    'reference'  => 'Request'
                ]);
            }

            $statusApproved = ProductOrderStatus::where('name', 'Processed')->first();
            $order->update(['product_order_status_id' => $statusApproved->id]);

            try {
                Mail::to($order->user->email)->send(new OrderNotification($order, 'Disetujui'));
            } catch (\Exception $e) {
                Log::error("Mail Error: " . $e->getMessage());
            }

            AuditLog::create(['user_id' => auth()->id(), 'action' => "APPROVE ORDER: Menyetujui pesanan #{$id}"]);

            return response()->json(['message' => 'Pesanan berhasil disetujui dan stok telah dipotong']);
        });
    }

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

    public function reject($id) {
        $statusRejected = ProductOrderStatus::where('name', 'Rejected')->first();
        $order = ProductOrder::findOrFail($id);
        $order->update(['product_order_status_id' => $statusRejected->id]);

        AuditLog::create(['user_id' => auth()->id(), 'action' => "REJECT ORDER: Menolak pesanan #{$id}"]);
        return response()->json(['message' => 'Pesanan telah ditolak']);
    }

    public function cancel($id) {
        return DB::transaction(function() use ($id) {
            $order = ProductOrder::with(['items', 'status'])->lockForUpdate()->findOrFail($id);

            $invalidStatusNames = ['Shipping', 'Completed'];
            $invalidStatusIds = ProductOrderStatus::whereIn('name', $invalidStatusNames)->pluck('id')->toArray();

            if (in_array($order->product_order_status_id, $invalidStatusIds)) {
                return response()->json(['message' => 'Pesanan sudah dalam pengiriman dan tidak bisa dibatalkan'], 422);
            }

            $statusApproved = ProductOrderStatus::where('name', 'Processed')->first();
            if ($order->product_order_status_id === $statusApproved->id) {
                foreach ($order->items as $item) {
                    Product::find($item->product_id)->increment('stock', $item->quantity);

                    StockLog::create([
                        'product_id' => $item->product_id,
                        'user_id'    => auth()->id(),
                        'type'       => 'in',
                        'quantity'   => $item->quantity,
                        'reference'  => 'Manual'
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
