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
use App\Services\SyncReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class ProductOrderController extends Controller
{
    // ============================================================
    // HELPER: Inisialisasi Midtrans config
    // ============================================================
    private function initMidtrans(): void
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$clientKey    = config('midtrans.client_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;
    }

    // ============================================================
    // HELPER: Buat Snap token
    // PENTING: Dipanggil SETELAH transaction commit, bukan di dalam transaction
    // ============================================================
    private function generateSnapToken(ProductOrder $order): ?string
    {
        try {
            $this->initMidtrans();

            $paymentRef = 'ORD-' . strtoupper(substr(str_replace('-', '', $order->id), 0, 8)) . '-' . time();

            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id'       => (string) $item->product_id,
                    'price'    => (int) $item->price_at_order,
                    'quantity' => (int) $item->quantity,
                    'name'     => substr($item->product->name ?? 'Produk', 0, 50),
                ];
            }

            if ($order->product_order_delivery_cost > 0) {
                $itemDetails[] = [
                    'id' => 'DELIVERY', 'price' => (int) $order->product_order_delivery_cost,
                    'quantity' => 1, 'name' => 'Biaya Pengiriman',
                ];
            }

            if ($order->product_order_discount > 0) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT', 'price' => -(int) $order->product_order_discount,
                    'quantity' => 1, 'name' => 'Diskon',
                ];
            }

            $grandTotal = collect($itemDetails)->sum(fn($i) => $i['price'] * $i['quantity']);

            $params = [
                'transaction_details' => [
                    'order_id'     => $paymentRef,
                    'gross_amount' => $grandTotal,
                ],
                'item_details'     => $itemDetails,
                'customer_details' => [
                    'first_name' => $order->user->name  ?? 'Customer',
                    'email'      => $order->user->email ?? '',
                    'phone'      => $order->user->phone ?? '',
                ],
                'callbacks' => ['finish' => url('/customer/history')],
            ];

            $snapToken = Snap::getSnapToken($params);

            ProductOrder::where('id', $order->id)->update([
                'payment_token' => $snapToken,
                'payment_ref'   => $paymentRef,
            ]);

            Log::info('Snap token generated', [
                'order_id'    => $order->id,
                'payment_ref' => $paymentRef,
            ]);

            return $snapToken;

        } catch (\Exception $e) {
            Log::error('generateSnapToken gagal: ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================
    // INDEX — daftar order
    // ============================================================
    public function index()
    {
        try {
            $user  = auth()->user();
            $query = ProductOrder::with([
                'status',
                'type',
                'items.product.warehouse',
                'items.product.rack',
                'user',
                'delivery.status',
                'delivery.courier',
            ])->latest();

            if ($user->hasRole('customer')) {
                $query->where('user_id', $user->id);
            }

            return response()->json($query->get(), 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // STORE — checkout keranjang oleh customer
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'regency'             => 'required|string',
            'district'            => 'required|string',
            'village'             => 'required|string',
            'use_profile_address' => 'required|boolean',
            'shipping_address'    => 'nullable|string',
            'phone_order'         => 'nullable|string|max:20',
            'request_type'        => 'required',
        ]);

        $userId    = auth()->id();
        $user      = auth()->user();
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 422);
        }

        $finalAddress = $request->use_profile_address ? $user->address : $request->shipping_address;

        if (empty($finalAddress)) {
            return response()->json(['message' => 'Alamat pengiriman tidak boleh kosong'], 422);
        }

        $totalQuantity = 0;
        $subTotal      = 0;
        $anyBulky      = false;

        foreach ($cartItems as $item) {
            $totalQuantity += (int) $item->quantity;
            $subTotal      += ($item->product->price * $item->quantity);
            if ($item->product->is_bulky) $anyBulky = true;
        }

        $typeId      = ($totalQuantity > 50 || $anyBulky) ? 2 : 1;
        $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

        $awaitingStatus     = ProductOrderStatus::where('name', 'Awaiting Payment')->firstOrFail();
        $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
        $deliveryMethod     = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

        $order = DB::transaction(function () use (
            $request, $userId, $cartItems, $finalAddress,
            $typeId, $vehicleName, $subTotal, $awaitingStatus, $deliveryMethod
        ) {
            $order = ProductOrder::create([
                'user_id'                     => $userId,
                'product_order_status_id'     => $awaitingStatus->id,
                'product_order_type_id'       => $typeId,
                'product_order_delivery_id'   => $deliveryMethod->id,
                'product_order_delivery_cost' => 0,
                'product_order_discount'      => 0,
                'required_vehicle'            => $vehicleName,
                'regency'                     => $request->regency,
                'district'                    => $request->district,
                'village'                     => $request->village,
                'shipping_address'            => $finalAddress,
                'phone_order'                 => $request->phone_order,
                'notes'                       => $request->notes,
                'total'                       => $subTotal,
                'payment_status'              => 'unpaid',
                'payment_method'              => 'snap',
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

            return $order;
        });

        $order->load(['items.product', 'user', 'status']);
        $snapToken = $this->generateSnapToken($order);

        // Sync ke report_service
        app(SyncReportService::class)->syncOrder($order->fresh(['status', 'items.product']));

        return response()->json([
            'message'    => 'Pesanan berhasil dibuat!',
            'order_id'   => $order->id,
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ], 201);
    }

    // ============================================================
    // QUICK STORE — pesanan instan dari welcome modal
    // ============================================================
    public function quickStore(Request $request)
    {
        $request->validate([
            'product_id'          => 'required|exists:products,id',
            'quantity'            => 'required|integer|min:1',
            'regency'             => 'required|string',
            'district'            => 'required|string',
            'village'             => 'required|string',
            'request_type'        => 'required|in:delivery,self_pickup',
            'use_profile_address' => 'required|boolean',
            'shipping_address'    => 'nullable|string',
            'phone_order'         => 'nullable|string|max:20',
            'notes'               => 'nullable|string',
        ]);

        $user    = auth()->user();
        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi'], 422);
        }

        $finalAddress = $request->use_profile_address ? $user->address : $request->shipping_address;

        if (empty($finalAddress)) {
            return response()->json(['message' => 'Alamat profil Anda kosong. Harap isi profil atau input alamat manual.'], 422);
        }

        $typeId      = ($request->quantity > 50 || $product->is_bulky) ? 2 : 1;
        $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

        $awaitingStatus     = ProductOrderStatus::where('name', 'Awaiting Payment')->firstOrFail();
        $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
        $deliveryMethod     = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

        $order = DB::transaction(function () use (
            $request, $user, $product, $finalAddress,
            $typeId, $vehicleName, $awaitingStatus, $deliveryMethod
        ) {
            $order = ProductOrder::create([
                'user_id'                     => $user->id,
                'product_order_status_id'     => $awaitingStatus->id,
                'product_order_type_id'       => $typeId,
                'product_order_delivery_id'   => $deliveryMethod->id,
                'product_order_delivery_cost' => 0,
                'product_order_discount'      => 0,
                'required_vehicle'            => $vehicleName,
                'regency'                     => $request->regency,
                'district'                    => $request->district,
                'village'                     => $request->village,
                'shipping_address'            => $finalAddress,
                'phone_order'                 => $request->phone_order,
                'notes'                       => $request->notes ?? 'Pesanan Instan',
                'total'                       => $product->price * $request->quantity,
                'payment_status'              => 'unpaid',
                'payment_method'              => 'snap',
            ]);

            ProductOrderDetail::create([
                'product_order_id' => $order->id,
                'product_id'       => $product->id,
                'quantity'         => $request->quantity,
                'price_at_order'   => $product->price,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action'  => "QUICK ORDER: Pesanan instan #{$order->id} ({$product->name})",
            ]);

            return $order;
        });

        $order->load(['items.product', 'user', 'status']);
        $snapToken = $this->generateSnapToken($order);

        // Sync ke report_service
        app(SyncReportService::class)->syncOrder($order->fresh(['status', 'items.product']));

        return response()->json([
            'message'    => 'Pesanan berhasil dibuat!',
            'order_id'   => $order->id,
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ], 201);
    }

    // ============================================================
    // ADMIN STORE — admin buat order untuk customer
    // ============================================================
    public function adminStore(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:users,id',
            'products'              => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
            'request_type'          => 'required|in:delivery,self_pickup',
            'payment_method'        => 'required|in:snap,cash',
            'courier_id'            => 'nullable|exists:users,id',
            'notes'                 => 'nullable|string',
            'address.regency'       => 'nullable|string',
            'address.district'      => 'nullable|string',
            'address.village'       => 'nullable|string',
            'address.detail'        => 'nullable|string',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $totalQuantity = 0;
                $totalPrice    = 0;
                $anyBulky      = false;
                $productsData  = [];

                foreach ($request->products as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $qty     = (int) $item['quantity'];

                    if ($product->stock < $qty) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi");
                    }

                    $subtotal       = $product->price * $qty;
                    $totalQuantity += $qty;
                    $totalPrice    += $subtotal;

                    if ($product->is_bulky) $anyBulky = true;

                    $productsData[] = [
                        'product'  => $product,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                    ];
                }

                $typeId      = ($totalQuantity > 50 || $anyBulky) ? 2 : 1;
                $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle';

                $deliveryMethodName = ($request->request_type == 'self_pickup') ? 'Self Pickup' : 'Delivery';
                $deliveryMethod     = ProductOrderDelivery::where('name', $deliveryMethodName)->first();

                $shippingAddress = $request->input('address.detail', '');
                if (empty($shippingAddress)) {
                    $customer        = User::find($request->customer_id);
                    $shippingAddress = $customer?->address ?? '';
                }

                $isCash = $request->payment_method === 'cash';

                $initialStatus = $isCash
                    ? ProductOrderStatus::where('name', 'Pending')->firstOrFail()
                    : ProductOrderStatus::where('name', 'Awaiting Payment')->firstOrFail();

                $order = ProductOrder::create([
                    'user_id'                     => $request->customer_id,
                    'product_order_status_id'     => $initialStatus->id,
                    'product_order_type_id'       => $typeId,
                    'product_order_delivery_id'   => $deliveryMethod->id,
                    'product_order_delivery_cost' => 0,
                    'product_order_discount'      => 0,
                    'required_vehicle'            => $vehicleName,
                    'notes'                       => $request->notes ?? 'Admin Manual Order',
                    'total'                       => $totalPrice,
                    'regency'                     => $request->input('address.regency',  ''),
                    'district'                    => $request->input('address.district', ''),
                    'village'                     => $request->input('address.village',  ''),
                    'shipping_address'            => $shippingAddress,
                    'phone_order'                 => $request->phone_order,
                    'payment_method'              => $isCash ? 'cash' : 'snap',
                    'payment_status' => 'unpaid', // ← selalu mulai unpaid
                    'paid_at'        => null,     // ← belum bayar
                ]);

                foreach ($productsData as $item) {
                    ProductOrderDetail::create([
                        'product_order_id' => $order->id,
                        'product_id'       => $item['product']->id,
                        'quantity'         => $item['quantity'],
                        'price_at_order'   => $item['product']->price,
                    ]);
                }

                if ($request->courier_id && $request->request_type === 'delivery') {
                    $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();
                    Delivery::create([
                        'product_order_id'   => $order->id,
                        'courier_id'         => $request->courier_id,
                        'delivery_status_id' => $claimedStatus->id,
                        'tracking_number'    => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
                    ]);
                    $order->update([
                        'product_order_status_id' => ProductOrderStatus::where('name', 'Shipping')->first()->id,
                    ]);
                }

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "ADMIN ORDER: Buat pesanan #{$order->id} untuk customer #{$request->customer_id} [{$request->payment_method}]",
                ]);

                return $order;
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Sync ke report_service setelah transaction commit
        app(SyncReportService::class)->syncOrder($order->fresh(['status', 'items.product']));

        return response()->json([
            'message'  => 'Pesanan berhasil dibuat',
            'order_id' => $order->id,
        ], 201);
    }

    // ============================================================
    // CONFIRM CASH PAYMENT — operator konfirmasi bayar tunai
    // ============================================================
    public function confirmCashPayment($id)
    {
        try {
            $order = ProductOrder::with('status')->findOrFail($id);

            if ($order->payment_method !== 'cash') {
                return response()->json(['message' => 'Hanya pesanan tunai yang bisa dikonfirmasi manual.'], 422);
            }

            if ($order->payment_status === 'cash') {
                return response()->json(['message' => 'Pembayaran sudah dikonfirmasi sebelumnya.'], 422);
            }

            $order->update([
                'payment_status' => 'cash',
                'paid_at'        => now(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "KONFIRMASI BAYAR TUNAI: Pesanan #{$id} dikonfirmasi lunas oleh operator.",
            ]);

            // Sync ke report_service
            app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

            return response()->json(['message' => 'Pembayaran tunai berhasil dikonfirmasi.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal konfirmasi: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // APPROVE — admin setujui pesanan
    // ============================================================
    public function approve(Request $request, $id)
    {
        $request->validate([
            'estimated_delivery_start' => 'required|date',
            'estimated_delivery_end'   => 'required|date|after_or_equal:estimated_delivery_start',
        ]);

        $order = DB::transaction(function () use ($request, $id) {
            $order = ProductOrder::with(['items.product', 'user'])->lockForUpdate()->findOrFail($id);

            $pendingStatus = ProductOrderStatus::where('name', 'Pending')->first();

            if ($order->product_order_status_id !== $pendingStatus->id) {
                return response()->json(['message' => 'Pesanan belum berstatus Pending / sudah diproses'], 422);
            }

            if (!in_array($order->payment_status, ['paid', 'cash'])) {
                return response()->json(['message' => 'Pesanan belum dibayar, tidak dapat disetujui'], 422);
            }

            foreach ($order->items as $item) {
                $p = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if ($p->stock < $item->quantity) {
                    throw new \Exception("Stok {$p->name} tidak cukup");
                }
                $p->decrement('stock', $item->quantity);
                StockLog::create([
                    'product_id' => $p->id,
                    'user_id'    => auth()->id(),
                    'type'       => 'out',
                    'quantity'   => $item->quantity,
                    'reference'  => 'Request',
                ]);

                // Sync stok produk yang berkurang ke report_service
                app(SyncReportService::class)->syncProduct($p->fresh());
            }

            $order->update([
                'product_order_status_id'  => ProductOrderStatus::where('name', 'Processed')->first()->id,
                'estimated_delivery_start' => $request->estimated_delivery_start,
                'estimated_delivery_end'   => $request->estimated_delivery_end,
            ]);

            $deliveryExists = Delivery::where('product_order_id', $order->id)->exists();
            if (!$deliveryExists) {
                Delivery::create([
                    'product_order_id'   => $order->id,
                    'courier_id'         => null,
                    'delivery_status_id' => DeliveryStatus::where('name', 'Ready')->first()->id,
                    'tracking_number'    => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
                ]);
            }

            try {
                Mail::to($order->user->email)->send(new OrderNotification($order, 'Disetujui'));
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email: ' . $e->getMessage());
            }

            return $order;
        });

        // Sync order status ke report_service
        app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

        return response()->json(['message' => 'Pesanan disetujui dan siap dijemput kurir']);
    }

    // ============================================================
    // REJECT — admin tolak pesanan → refund otomatis
    // ============================================================
    public function reject($id)
    {
        $order = DB::transaction(function () use ($id) {
            $order = ProductOrder::with(['items.product', 'user'])->findOrFail($id);

            $rejectedStatus = ProductOrderStatus::where('name', 'Rejected')->firstOrFail();

            if ($order->payment_status === 'paid' && $order->payment_ref) {
                try {
                    $this->initMidtrans();
                    \Midtrans\Transaction::cancel($order->payment_ref);
                    $order->update([
                        'product_order_status_id' => $rejectedStatus->id,
                        'payment_status'           => 'refunded',
                    ]);
                } catch (\Exception $e) {
                    try {
                        \Midtrans\Transaction::refund($order->payment_ref, [
                            'refund_key' => 'REFUND-' . $order->payment_ref,
                            'amount'     => (int) $order->total,
                            'reason'     => 'Pesanan ditolak oleh admin',
                        ]);
                        $order->update([
                            'product_order_status_id' => $rejectedStatus->id,
                            'payment_status'           => 'refunded',
                        ]);
                    } catch (\Exception $e2) {
                        Log::error('Refund gagal: ' . $e2->getMessage());
                        $order->update(['product_order_status_id' => $rejectedStatus->id]);
                    }
                }
            } else {
                $order->update(['product_order_status_id' => $rejectedStatus->id]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "Tolak pesanan #{$id}" . ($order->payment_status === 'refunded' ? ' + refund diproses' : ''),
            ]);

            try {
                $order->load(['user', 'items.product']);
                Mail::to($order->user->email)->send(new OrderNotification($order, 'Ditolak'));
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email penolakan pesanan #' . $id . ': ' . $e->getMessage());
            }

            return $order;
        });

        // Sync order status ke report_service
        app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

        return response()->json(['message' => 'Pesanan ditolak' . ($order->payment_status === 'refunded' ? ' dan refund diproses' : '')]);
    }

    // ============================================================
    // CANCEL — customer batalkan pesanan
    // ============================================================
    public function cancel($id)
    {
        $order = DB::transaction(function () use ($id) {
            $order = ProductOrder::with(['items', 'status'])->findOrFail($id);

            if (in_array($order->status->name, ['Shipping', 'Completed', 'Processed'])) {
                return response()->json(['message' => 'Pesanan sudah diproses, tidak bisa dibatalkan'], 422);
            }

            $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->firstOrFail();

            if ($order->payment_status === 'paid' && $order->payment_ref) {
                try {
                    $this->initMidtrans();
                    \Midtrans\Transaction::cancel($order->payment_ref);
                    $order->update([
                        'product_order_status_id' => $cancelledStatus->id,
                        'payment_status'           => 'refunded',
                    ]);
                } catch (\Exception $e) {
                    try {
                        \Midtrans\Transaction::refund($order->payment_ref, [
                            'refund_key' => 'REFUND-CANCEL-' . $order->payment_ref,
                            'amount'     => (int) $order->total,
                            'reason'     => 'Dibatalkan oleh customer',
                        ]);
                        $order->update([
                            'product_order_status_id' => $cancelledStatus->id,
                            'payment_status'           => 'refunded',
                        ]);
                    } catch (\Exception $e2) {
                        Log::error('Cancel refund gagal: ' . $e2->getMessage());
                        $order->update(['product_order_status_id' => $cancelledStatus->id]);
                    }
                }
            } else {
                $order->update([
                    'product_order_status_id' => $cancelledStatus->id,
                    'payment_token'            => null,
                    'payment_ref'              => null,
                ]);
            }

            return $order;
        });

        // Sync order status ke report_service
        app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

        return response()->json([
            'message' => $order->payment_status === 'refunded'
                ? 'Pesanan dibatalkan dan refund sedang diproses'
                : 'Pesanan dibatalkan',
        ]);
    }

    // ============================================================
    // GET PAYMENT TOKEN — untuk tombol Bayar Sekarang di history
    // ============================================================
    public function getPaymentToken($id)
    {
        $order = ProductOrder::with(['items.product', 'user'])->findOrFail($id);

        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->payment_token) {
            return response()->json([
                'snap_token' => $order->payment_token,
                'client_key' => config('midtrans.client_key'),
            ]);
        }

        $snapToken = $this->generateSnapToken($order);

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    // ============================================================
    // WEBHOOK — notifikasi dari Midtrans (publik, tanpa auth)
    // ============================================================
    public function webhook(Request $request)
    {
        try {
            $this->initMidtrans();

            $notif       = new \Midtrans\Notification();
            $orderId     = $notif->order_id;
            $transStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;

            Log::info('Webhook Midtrans masuk', [
                'order_id'     => $orderId,
                'trans_status' => $transStatus,
                'fraud_status' => $fraudStatus,
            ]);

            $order = ProductOrder::where('payment_ref', $orderId)->first();

            if (!$order) {
                Log::warning('Webhook: order tidak ditemukan', ['payment_ref' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            if (in_array($transStatus, ['capture', 'settlement'])) {
                if ($fraudStatus == 'accept' || $fraudStatus == null) {
                    $order->update([
                        'payment_status'          => 'paid',
                        'paid_at'                 => now(),
                        'product_order_status_id' => ProductOrderStatus::where('name', 'Pending')->first()->id,
                    ]);
                    Log::info('Webhook: order dibayar', ['order_id' => $order->id]);

                    // Sync ke report_service setelah pembayaran berhasil
                    app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));
                }
            } elseif (in_array($transStatus, ['cancel', 'deny', 'expire'])) {
                $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->first();
                $order->update([
                    'payment_status'          => 'unpaid',
                    'product_order_status_id' => $cancelledStatus->id,
                    'payment_token'           => null,
                    'payment_ref'             => null,
                ]);
                Log::info('Webhook: pembayaran gagal/expire', ['order_id' => $order->id]);

                // Sync ke report_service
                app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));
            }

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    // ============================================================
    // SHOW — detail order
    // ============================================================
    public function show($id)
    {
        try {
            $order = ProductOrder::with([
                'status',
                'type',
                'items.product.warehouse',
                'items.product.rack',
                'user',
                'delivery.status',
                'delivery.courier',
            ])->findOrFail($id);

            return response()->json($order, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }
    }

    // ============================================================
    // COMPLETE PICKUP — operator tandai sudah diambil kurir
    // ============================================================
    public function completePickup($id)
    {
        try {
            $order = ProductOrder::findOrFail($id);
            $shippingStatus = ProductOrderStatus::where('name', 'Shipping')->firstOrFail();
            $order->update(['product_order_status_id' => $shippingStatus->id]);

            // Sync ke report_service
            app(SyncReportService::class)->syncOrderStatus($order->fresh(['status']));

            return response()->json(['message' => 'Status pesanan diperbarui ke Shipping']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal update status: ' . $e->getMessage()], 500);
        }
    }
}
