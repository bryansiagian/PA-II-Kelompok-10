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
    public function makeReady($id, Request $request) {
        return DB::transaction(function() use ($id, $request) {

            $order = ProductOrder::findOrFail($id);
            $delivery = Delivery::where('product_order_id', $order->id)->firstOrFail();

            $activeStatusIds = DeliveryStatus::whereIn('name', ['Claimed', 'In Transit'])
                ->pluck('id');

            // Validasi: kurir sedang aktif di delivery lain
            $courierBusy = Delivery::where('courier_id', $request->courier_id)
                ->where('id', '!=', $delivery->id)
                ->whereIn('delivery_status_id', $activeStatusIds)
                ->exists();

            if ($courierBusy) {
                return response()->json([
                    'message' => 'Kurir ini sedang menangani pengiriman lain yang belum selesai.'
                ], 422);
            }

            // Validasi: kendaraan sedang dipakai di delivery lain
            $vehicleBusy = Delivery::where('vehicle_id', $request->vehicle_id)
                ->where('id', '!=', $delivery->id)
                ->whereIn('delivery_status_id', $activeStatusIds)
                ->exists();

            if ($vehicleBusy) {
                return response()->json([
                    'message' => 'Kendaraan ini sedang digunakan kurir lain.'
                ], 422);
            }

            $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();

            $delivery->update([
                'courier_id'         => $request->courier_id,
                'vehicle_id'         => $request->vehicle_id,
                'delivery_status_id' => $claimedStatus->id,
            ]);

            $order->update([
                'product_order_status_id' => ProductOrderStatus::where('name', 'Shipping')->first()->id,
            ]);

            return response()->json(['message' => 'Kurir berhasil ditugaskan']);
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
                    'message' => 'Sudah diambil kurir lain'
                ], 422);
            }

            $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();

            $delivery->update([
                'courier_id'         => auth()->id(),
                'delivery_status_id' => $claimedStatus->id,
            ]);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Gudang Pusat',
                'description' => 'Kurir ' . auth()->user()->name . ' telah mengonfirmasi pengambilan paket.',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diambil',
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
        $delivery = Delivery::where('id', $id)
            ->where('courier_id', auth()->id())
            ->firstOrFail();

        $inTransitStatus = DeliveryStatus::where('name', 'In Transit')->first();

        $delivery->update([
            'delivery_status_id' => $inTransitStatus->id,
        ]);

        ShipmentTracking::create([
            'delivery_id' => $delivery->id,
            'location'    => 'Dalam Perjalanan',
            'description' => 'Kurir sedang menuju lokasi tujuan.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status: Dalam perjalanan',
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
            'image'             => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'receiver_name'     => 'required|string',
            'receiver_relation' => 'required|string',
            'delivery_note'     => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $delivery = Delivery::with('order.user')
                ->where('id', $id)
                ->where('courier_id', auth()->id())
                ->firstOrFail();

            /*
            =====================================================
            UPLOAD FOTO — simpan ke storage/app/public/uploads/proofs
            =====================================================
            */
            $photoPath = null;

            if ($request->hasFile('image')) {
                $file      = $request->file('image');
                $filename  = time() . '_' . $file->getClientOriginalName();
                $photoPath = $file->storeAs('uploads/proofs', $filename, 'public');
            }

            /*
            =====================================================
            DELIVERED STATUS
            =====================================================
            */
            $deliveredStatus = DeliveryStatus::where('name', 'Delivered')->first();

            $delivery->update([
                'delivery_status_id' => $deliveredStatus->id,
                'image'              => $photoPath,
                'receiver_name'      => $request->receiver_name,
                'receiver_relation'  => $request->receiver_relation,
                'delivery_note'      => $request->delivery_note,
                'delivered_at'       => now(),
            ]);

            /*
            =====================================================
            UPDATE ORDER STATUS
            =====================================================
            */
            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();

            if ($delivery->order && $completedStatus) {
                $delivery->order->update([
                    'product_order_status_id' => $completedStatus->id,
                ]);
            }

            /*
            =====================================================
            TRACKING HISTORY
            =====================================================
            */
            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => $delivery->order->shipping_address
                                 ?? $delivery->order->user->address
                                 ?? 'Lokasi Tujuan',
                'description' => "Paket diterima oleh {$request->receiver_name} ({$request->receiver_relation})",
            ]);

            /*
            =====================================================
            AUDIT LOG
            =====================================================
            */
            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "DELIVERED: Pengiriman selesai #{$delivery->tracking_number}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diselesaikan',
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

        $claimedID   = DeliveryStatus::where('name', 'Claimed')->first()?->id;
        $transitID   = DeliveryStatus::where('name', 'In Transit')->first()?->id;
        $deliveredID = DeliveryStatus::where('name', 'Delivered')->first()?->id;

        return response()->json([
            'active' => Delivery::where('courier_id', $userId)
                ->whereIn('delivery_status_id', [$claimedID, $transitID])
                ->count(),

            'completed' => Delivery::where('courier_id', $userId)
                ->where('delivery_status_id', $deliveredID)
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
        $readyStatusID = DeliveryStatus::where('name', 'Ready')->first()?->id;

        return response()->json(
            Delivery::with([
                'order.user',
                'order.items.product',
                'status',
                'vehicle',
            ])
            ->where('delivery_status_id', $readyStatusID)
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
        $claimedID = DeliveryStatus::where('name', 'Claimed')->first()?->id;
        $transitID = DeliveryStatus::where('name', 'In Transit')->first()?->id;

        return response()->json(
            Delivery::with([
                'order.user',
                'order.items.product',
                'status',
                'vehicle',
            ])
            ->where('courier_id', auth()->id())
            ->whereIn('delivery_status_id', [$claimedID, $transitID])
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

            $deliveredID = DeliveryStatus::where('name', 'Delivered')->first()?->id;

            $deliveries = Delivery::with([
                'order.user',
                'order.items.product',
                'status',
                'vehicle',
            ])
            ->where('courier_id', auth()->id())
            ->where('delivery_status_id', $deliveredID)
            ->latest()
            ->get();

            return response()->json($deliveries, 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Gagal memuat riwayat',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
