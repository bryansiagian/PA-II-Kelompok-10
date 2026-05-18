<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\ProductOrder;
use App\Models\ProductOrderStatus;
use App\Models\DeliveryStatus;
use App\Models\ShipmentTracking;
use App\Models\AuditLog;
use App\Models\CourierDetail;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{

    /**
     * =========================================================
     * TRACKING DELIVERY
     * =========================================================
     */
    public function getTracking($id)
    {
        return response()->json(

            Delivery::with([
                'order.user',
                'status',
                'courier',
                'vehicle',
                'trackings' => fn($q) => $q->latest()
            ])->findOrFail($id)

        );
    }

    /**
     * =========================================================
     * READY DELIVERY
     * =========================================================
     */
    public function makeReady(Request $request, $id)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'courier_id' => 'nullable|exists:users,id',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $order = ProductOrder::findOrFail($id);

            $vehicle = Vehicle::findOrFail(
                $request->vehicle_id
            );

            /*
            =====================================================
            STATUS
            =====================================================
            */

            $readyStatus = DeliveryStatus::where(
                'name',
                'Ready'
            )->first();

            $claimedStatus = DeliveryStatus::where(
                'name',
                'Claimed'
            )->first();

            $courierId = $request->courier_id;

            $finalStatus = $courierId
                ? $claimedStatus->id
                : $readyStatus->id;

            /*
            =====================================================
            CREATE DELIVERY
            =====================================================
            */

            $delivery = Delivery::create([

                'product_order_id' => $order->id,

                'courier_id' => $courierId,

                'vehicle_id' => $request->vehicle_id,

                'delivery_status_id' => $finalStatus,

                'tracking_number' =>
                    'TRK-' .
                    strtoupper(
                        bin2hex(random_bytes(4))
                    ),
            ]);

            /*
            =====================================================
            UPDATE ORDER STATUS
            =====================================================
            */

            $shippingStatus =
                ProductOrderStatus::where(
                    'name',
                    'Shipping'
                )->first();

            if ($shippingStatus) {

                $order->update([
                    'product_order_status_id' =>
                        $shippingStatus->id
                ]);
            }

            /*
            =====================================================
            VEHICLE DESCRIPTION
            =====================================================
            */

            $vehicleDesc =
                "{$vehicle->brand} {$vehicle->subtype} ({$vehicle->plate_number}) warna {$vehicle->color}";

            $description = $courierId

                ? "Pesanan ditugaskan kepada kurir menggunakan {$vehicleDesc}."

                : "Pesanan siap dijemput menggunakan {$vehicleDesc}.";

            /*
            =====================================================
            TRACKING
            =====================================================
            */

            ShipmentTracking::create([

                'delivery_id' => $delivery->id,

                'location' => 'Gudang Pusat',

                'description' => $description,
            ]);

            /*
            =====================================================
            AUDIT
            =====================================================
            */

            AuditLog::create([

                'user_id' => auth()->id(),

                'action' =>
                    "READY: Paket #{$id} disiapkan dengan kendaraan {$vehicle->plate_number}",
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Resi diterbitkan. Pesanan siap didistribusikan.'
            ]);
        });
    }

    /**
     * =========================================================
     * CLAIM DELIVERY
     * =========================================================
     */
    public function claim($id)
    {
        return DB::transaction(function () use ($id) {

            $delivery = Delivery::lockForUpdate()
                ->findOrFail($id);

            if ($delivery->courier_id) {

                return response()->json([
                    'message' =>
                        'Sudah diambil kurir lain'
                ], 422);
            }

            $claimedStatus = DeliveryStatus::where(
                'name',
                'Claimed'
            )->first();

            $delivery->update([

                'courier_id' => auth()->id(),

                'delivery_status_id' =>
                    $claimedStatus->id
            ]);

            ShipmentTracking::create([

                'delivery_id' => $delivery->id,

                'location' => 'Gudang Pusat',

                'description' =>
                    'Kurir ' .
                    auth()->user()->name .
                    ' telah mengonfirmasi pengambilan paket.'
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Tugas berhasil diambil'
            ]);
        });
    }

    /**
     * =========================================================
     * START SHIPPING
     * =========================================================
     */
    public function startShipping($id)
    {
        $delivery = Delivery::where(
                'id',
                $id
            )
            ->where(
                'courier_id',
                auth()->id()
            )
            ->firstOrFail();

        $inTransitStatus =
            DeliveryStatus::where(
                'name',
                'In Transit'
            )->first();

        $delivery->update([
            'delivery_status_id' =>
                $inTransitStatus->id
        ]);

        ShipmentTracking::create([

            'delivery_id' => $delivery->id,

            'location' => 'Dalam Perjalanan',

            'description' =>
                'Kurir sedang menuju lokasi tujuan.'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status: Dalam perjalanan'
        ]);
    }

    /**
     * =========================================================
     * COMPLETE DELIVERY
     * =========================================================
     */
    public function complete(Request $request, $id)
    {
        $request->validate([

            'image' =>
                'required|image|mimes:jpg,jpeg,png|max:4096',

            'receiver_name' =>
                'required|string',

            'receiver_relation' =>
                'required|string',

            'notes' =>
                'nullable|string'
        ]);

        return DB::transaction(function () use ($request, $id) {

            $delivery = Delivery::with(
                    'order.user'
                )
                ->where('id', $id)
                ->where(
                    'courier_id',
                    auth()->id()
                )
                ->firstOrFail();

            /*
            =====================================================
            UPLOAD FOTO
            =====================================================
            */

            $photoPath = null;

            if ($request->hasFile('image')) {

                $file = $request->file('image');

                $filename =
                    time() . '_' .
                    $file->getClientOriginalName();

                $destination =
                    public_path('uploads/proofs');

                if (!file_exists($destination)) {

                    mkdir(
                        $destination,
                        0777,
                        true
                    );
                }

                $file->move(
                    $destination,
                    $filename
                );

                $photoPath =
                    'uploads/proofs/' . $filename;
            }

            /*
            =====================================================
            DELIVERED STATUS
            =====================================================
            */

            $deliveredStatus =
                DeliveryStatus::where(
                    'name',
                    'Delivered'
                )->first();

            $delivery->update([

                'delivery_status_id' =>
                    $deliveredStatus->id,

                'image' => $photoPath,

                'receiver_name' =>
                    $request->receiver_name,

                'receiver_relation' =>
                    $request->receiver_relation,

                'notes' =>
                    $request->notes,

                'delivered_at' =>
                    now(),
            ]);

            /*
            =====================================================
            UPDATE ORDER STATUS
            =====================================================
            */

            $completedStatus =
                ProductOrderStatus::where(
                    'name',
                    'Completed'
                )->first();

            if ($delivery->order && $completedStatus) {

                $delivery->order->update([

                    'product_order_status_id' =>
                        $completedStatus->id
                ]);
            }

            /*
            =====================================================
            TRACKING HISTORY
            =====================================================
            */

            ShipmentTracking::create([

                'delivery_id' =>
                    $delivery->id,

                'location' =>

                    $delivery->order->shipping_address

                    ?? $delivery->order->user->address

                    ?? 'Lokasi Tujuan',

                'description' =>

                    "Paket diterima oleh {$request->receiver_name} ({$request->receiver_relation})"
            ]);

            /*
            =====================================================
            AUDIT LOG
            =====================================================
            */

            AuditLog::create([

                'user_id' => auth()->id(),

                'action' =>

                    "DELIVERED: Pengiriman selesai #{$delivery->tracking_number}"
            ]);

            return response()->json([

                'success' => true,

                'message' =>
                    'Pengiriman berhasil diselesaikan'
            ]);
        });
    }

    /**
     * =========================================================
     * COURIER DASHBOARD STATS
     * =========================================================
     */
    public function getCourierStats()
    {
        $userId = auth()->id();

        $readyID = DeliveryStatus::where(
            'name',
            'Ready'
        )->first()?->id;

        $claimedID = DeliveryStatus::where(
            'name',
            'Claimed'
        )->first()?->id;

        $transitID = DeliveryStatus::where(
            'name',
            'In Transit'
        )->first()?->id;

        $deliveredID = DeliveryStatus::where(
            'name',
            'Delivered'
        )->first()?->id;

        return response()->json([

            'available' => Delivery::where(
                    'delivery_status_id',
                    $readyID
                )
                ->whereNull('courier_id')
                ->count(),

            'active' => Delivery::where(
                    'courier_id',
                    $userId
                )
                ->whereIn(
                    'delivery_status_id',
                    [$claimedID, $transitID]
                )
                ->count(),

            'completed' => Delivery::where(
                    'courier_id',
                    $userId
                )
                ->where(
                    'delivery_status_id',
                    $deliveredID
                )
                ->count(),
        ]);
    }

    /**
     * =========================================================
     * AVAILABLE DELIVERIES
     * =========================================================
     */
    public function getAvailableDeliveries()
    {
        $readyStatusID =
            DeliveryStatus::where(
                'name',
                'Ready'
            )->first()?->id;

        return response()->json(

            Delivery::with([
                'order.user',
                'order.items.product',
                'status',
                'vehicle'
            ])

            ->where(
                'delivery_status_id',
                $readyStatusID
            )

            ->whereNull('courier_id')

            ->get()
        );
    }

    /**
     * =========================================================
     * ACTIVE DELIVERIES
     * =========================================================
     */
    public function getActiveDeliveries()
    {
        $claimedID =
            DeliveryStatus::where(
                'name',
                'Claimed'
            )->first()?->id;

        $transitID =
            DeliveryStatus::where(
                'name',
                'In Transit'
            )->first()?->id;

        return response()->json(

            Delivery::with([
                'order.user',
                'order.items.product',
                'status',
                'vehicle'
            ])

            ->where(
                'courier_id',
                auth()->id()
            )

            ->whereIn(
                'delivery_status_id',
                [$claimedID, $transitID]
            )

            ->latest()

            ->get()
        );
    }

    /**
     * =========================================================
     * COURIER HISTORY
     * =========================================================
     */
    public function getCourierHistory()
    {
        try {

            $deliveredID =
                DeliveryStatus::where(
                    'name',
                    'Delivered'
                )->first()?->id;

            $deliveries = Delivery::with([

                    'order.user',

                    'order.items.product',

                    'status',

                    'vehicle'

                ])

                ->where(
                    'courier_id',
                    auth()->id()
                )

                ->where(
                    'delivery_status_id',
                    $deliveredID
                )

                ->latest()

                ->get();

            return response()->json(
                $deliveries,
                200
            );

        } catch (\Exception $e) {

            return response()->json([

                'message' =>
                    'Gagal memuat riwayat',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }
}
