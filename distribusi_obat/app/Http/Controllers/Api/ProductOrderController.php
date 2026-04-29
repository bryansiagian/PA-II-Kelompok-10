<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderNotification;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Product;
use App\Models\ProductOrderStatus;
use App\Models\ProductOrderDelivery;
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
                'items.product.rack',
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
     * Proses Checkout Keranjang
     */
    public function store(Request $request) {
        $request->validate([
            'regency' => 'required|string',
            'district' => 'required|string',
            'village' => 'required|string',
            'use_profile_address' => 'required|boolean',
            'shipping_address' => 'nullable|string',
            'request_type' => 'required'
        ]);

        return DB::transaction(function() use ($request) {
            $userId = auth()->id();
            $user = auth()->user();
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            if($cartItems->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong'], 422);
            }

            // AMBIL ALAMAT ASLI DARI TABEL USER JIKA DICENTANG
            $finalAddress = $request->use_profile_address
                            ? $user->address
                            : $request->shipping_address;

            if(empty($finalAddress)) {
                return response()->json(['message' => 'Alamat pengiriman tidak boleh kosong'], 422);
            }

            $totalQuantity = 0;
            $subTotal = 0;
            $anyBulky = false;

            foreach($cartItems as $item) {
                $totalQuantity += (int)$item->quantity;
                $subTotal += ($item->product->price * $item->quantity);
                if ($item->product->is_bulky) $anyBulky = true;
            }

            $typeId = ($totalQuantity > 50 || $anyBulky) ? 2 : 1;
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
                'required_vehicle'           => $vehicleName,
                'regency'                    => $request->regency,
                'district'                   => $request->district,
                'village'                    => $request->village,
                'shipping_address'           => $finalAddress,
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
     * Pesanan Instan / Quick Order (Welcome Modal)
     */
    public function quickStore(Request $request) {
        $request->validate([
            'product_id'          => 'required|exists:products,id',
            'quantity'            => 'required|integer|min:1',
            'regency'             => 'required|string',
            'district'            => 'required|string',
            'village'             => 'required|string',
            'request_type'        => 'required|in:delivery,self_pickup',
            'use_profile_address' => 'required|boolean',
            'shipping_address'    => 'nullable|string',
            'notes'               => 'nullable|string'
        ]);

        return DB::transaction(function() use ($request) {
            $user = auth()->user();
            $product = Product::findOrFail($request->product_id);

            if ($product->stock < $request->quantity) {
                return response()->json(['message' => 'Stok tidak mencukupi'], 422);
            }

            // LOGIC ALAMAT: Jika true, ambil $user->address dari database
            $finalAddress = $request->use_profile_address
                            ? $user->address
                            : $request->shipping_address;

            if(empty($finalAddress)) {
                return response()->json(['message' => 'Alamat profil Anda kosong. Harap isi profil atau input alamat manual.'], 422);
            }

            $typeId = ($request->quantity > 50 || $product->is_bulky) ? 2 : 1;
            $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

            $statusPending = ProductOrderStatus::where('name', 'Pending')->first();
            $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
            $deliveryMethod = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

            $order = ProductOrder::create([
                'user_id'                    => $user->id,
                'product_order_status_id'    => $statusPending->id,
                'product_order_type_id'      => $typeId,
                'product_order_delivery_id'  => $deliveryMethod->id,
                'product_order_delivery_cost'=> 0,
                'product_order_discount'     => 0,
                'required_vehicle'           => $vehicleName,
                'regency'                    => $request->regency,
                'district'                   => $request->district,
                'village'                    => $request->village,
                'shipping_address'           => $finalAddress,
                'notes'                      => $request->notes ?? 'Pesanan Instan',
                'total'                      => $product->price * $request->quantity
            ]);

            ProductOrderDetail::create([
                'product_order_id' => $order->id,
                'product_id'       => $product->id,
                'quantity'         => $request->quantity,
                'price_at_order'   => $product->price,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action'  => "QUICK ORDER: Pesanan instan #{$order->id} ({$product->name})"
            ]);

            return response()->json(['message' => 'Pesanan instan berhasil!', 'order_id' => $order->id], 201);
        });
    }

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
            if($product->stock < $request->quantity) return response()->json(['message' => "Stok habis"], 422);

            $typeId = ($request->quantity > 50 || $product->is_bulky) ? 2 : 1;
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
                'required_vehicle'           => $vehicleName,
                'notes'                      => $request->notes ?? 'Admin Manual Order',
                'total'                      => $product->price * $request->quantity
            ]);

            ProductOrderDetail::create(['product_order_id'=>$order->id, 'product_id'=>$product->id, 'quantity'=>$request->quantity, 'price_at_order'=>$product->price]);

            if ($request->courier_id && $request->request_type === 'delivery') {
                $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();
                Delivery::create(['product_order_id'=>$order->id, 'courier_id'=>$request->courier_id, 'delivery_status_id'=>$claimedStatus->id, 'tracking_number'=>'TRK-'.strtoupper(bin2hex(random_bytes(4)))]);
                $order->update(['product_order_status_id' => ProductOrderStatus::where('name', 'Shipping')->first()->id]);
            }
            return response()->json(['message' => 'Berhasil'], 201);
        });
    }

    public function approve($id) {
        return DB::transaction(function() use ($id) {
            $order = ProductOrder::with(['items.product', 'user'])->lockForUpdate()->findOrFail($id);
            if ($order->product_order_status_id !== ProductOrderStatus::where('name', 'Pending')->first()->id) return response()->json(['message' => 'Sudah diproses'], 422);

            foreach($order->items as $item) {
                $p = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if($p->stock < $item->quantity) throw new \Exception("Stok {$p->name} tidak cukup");
                $p->decrement('stock', $item->quantity);
                StockLog::create(['product_id'=>$p->id, 'user_id'=>auth()->id(), 'type'=>'out', 'quantity'=>$item->quantity, 'reference'=>'Request']);
            }
            $order->update(['product_order_status_id' => ProductOrderStatus::where('name', 'Processed')->first()->id]);
            try { Mail::to($order->user->email)->send(new OrderNotification($order, 'Disetujui')); } catch (\Exception $e) {}
            return response()->json(['message' => 'Disetujui']);
        });
    }

    public function reject($id) {
        ProductOrder::findOrFail($id)->update(['product_order_status_id' => ProductOrderStatus::where('name', 'Rejected')->first()->id]);
        return response()->json(['message' => 'Ditolak']);
    }

    public function cancel($id) {
        return DB::transaction(function() use ($id) {
            $order = ProductOrder::with(['items', 'status'])->findOrFail($id);
            if (in_array($order->status->name, ['Shipping', 'Completed'])) return response()->json(['message' => 'Sudah dikirim'], 422);

            if ($order->status->name === 'Processed') {
                foreach ($order->items as $item) {
                    Product::find($item->product_id)->increment('stock', $item->quantity);
                }
            }
            $order->update(['product_order_status_id' => ProductOrderStatus::where('name', 'Cancelled')->first()->id]);
            return response()->json(['message' => 'Dibatalkan']);
        });
    }
}
