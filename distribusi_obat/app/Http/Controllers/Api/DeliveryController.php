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
    public function makeReady($id, Request $request)
    {
        return DB::transaction(function () use ($id, $request) {

            $order    = ProductOrder::findOrFail($id);
            $delivery = Delivery::where('product_order_id', $order->id)->firstOrFail();

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

            $delivery = Delivery::lockForUpdate()->findOrFail($id);

            if ($delivery->courier_id) {
                return response()->json(['message' => 'Sudah diambil kurir lain'], 422);
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
     * START SHIPPING — kurir input estimasi tiba
     * =========================================================
     */
    public function startShipping(Request $request, $id)
    {
        $request->validate([
            'estimated_arrival' => 'required|date|after_or_equal:today',
        ]);

        $delivery = Delivery::where('id', $id)
            ->where('courier_id', auth()->id())
            ->firstOrFail();

        $inTransitStatus = DeliveryStatus::where('name', 'In Transit')->first();

        $delivery->update([
            'delivery_status_id' => $inTransitStatus->id,
            'estimated_arrival'  => $request->estimated_arrival,
            // reset flag kendala jika sebelumnya pernah dilaporkan delay lalu kurir ganti
            'is_delayed'         => false,
            'issue_type'         => null,
            'delay_reason'       => null,
            'delay_reported_at'  => null,
        ]);

        ShipmentTracking::create([
            'delivery_id' => $delivery->id,
            'location'    => 'Dalam Perjalanan',
            'description' => 'Kurir sedang menuju lokasi tujuan. Estimasi tiba: '
                             . \Carbon\Carbon::parse($request->estimated_arrival)->translatedFormat('d F Y') . '.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perjalanan dimulai. Estimasi tiba disimpan.',
        ]);
    }

    /**
     * =========================================================
     * REPORT ISSUE — kurir laporkan delay atau tidak bisa lanjut
     *
     * issue_type: 'delay' | 'cannot_continue'
     *   - delay          → status tetap In Transit, flag is_delayed = true
     *   - cannot_continue → kurir dilepas, delivery kembali ke status Ready,
     *                       order kembali ke status Processed (siap di-assign ulang)
     * =========================================================
     */
    public function reportIssue(Request $request, $id)
    {
        $request->validate([
            'issue_type' => 'required|in:delay,cannot_continue',
            'reason'     => 'required|string|max:500',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $delivery = Delivery::with('order')
                ->where('id', $id)
                ->where('courier_id', auth()->id())
                ->firstOrFail();

            if ($request->issue_type === 'delay') {

                /* ---- DELAY: tandai saja, kurir tetap bertugas ---- */
                $delivery->update([
                    'is_delayed'        => true,
                    'issue_type'        => 'delay',
                    'delay_reason'      => $request->reason,
                    'delay_reported_at' => now(),
                ]);

                ShipmentTracking::create([
                    'delivery_id' => $delivery->id,
                    'location'    => 'Dalam Perjalanan',
                    'description' => 'Kurir melaporkan keterlambatan: ' . $request->reason,
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "DELAY REPORTED: #{$delivery->tracking_number} — {$request->reason}",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Kendala delay berhasil dilaporkan.',
                ]);

            } else {

                /* ---- TIDAK BISA LANJUT: lepas kurir, kembalikan ke Ready ---- */
                $readyStatus     = DeliveryStatus::where('name', 'Ready')->first();
                $processedStatus = ProductOrderStatus::where('name', 'Processed')->first();

                $delivery->update([
                    'delivery_status_id' => $readyStatus->id,
                    'courier_id'         => null,
                    'vehicle_id'         => null,
                    'estimated_arrival'  => null,
                    'is_delayed'         => false,
                    'issue_type'         => 'cannot_continue',
                    'delay_reason'       => $request->reason,
                    'delay_reported_at'  => now(),
                ]);

                // Kembalikan status order agar operator bisa assign kurir baru
                if ($delivery->order) {
                    $delivery->order->update([
                        'product_order_status_id' => $processedStatus->id,
                    ]);
                }

                ShipmentTracking::create([
                    'delivery_id' => $delivery->id,
                    'location'    => 'Penugasan Ulang',
                    'description' => 'Kurir tidak dapat melanjutkan pengiriman: ' . $request->reason
                                     . '. Menunggu penugasan kurir pengganti.',
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action'  => "COURIER RELEASED: #{$delivery->tracking_number} — {$request->reason}",
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Laporan diterima. Pesanan akan di-assign ke kurir lain.',
                ]);
            }
        });
    }

    /**
     * =========================================================
     * RETURN DELIVERY — kurir kembalikan paket ke pengirim
     * =========================================================
     */
    public function returnDelivery(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $delivery = Delivery::with('order')
                ->where('id', $id)
                ->where('courier_id', auth()->id())
                ->firstOrFail();

            $inTransitStatus = DeliveryStatus::where('name', 'In Transit')->firstOrFail();
            if ($delivery->delivery_status_id !== $inTransitStatus->id) {
                return response()->json([
                    'message' => 'Hanya pengiriman yang sedang berjalan yang dapat dikembalikan.',
                ], 422);
            }

            $returnedStatus  = DeliveryStatus::where('name', 'Returned')->firstOrFail();
            $cancelledStatus = ProductOrderStatus::where('name', 'Cancelled')->firstOrFail();

            $delivery->update([
                'delivery_status_id' => $returnedStatus->id,
                'issue_type'         => 'returned',
                'delay_reason'       => $request->reason,
                'delay_reported_at'  => now(),
            ]);

            if ($delivery->order) {
                $delivery->order->update([
                    'product_order_status_id' => $cancelledStatus->id,
                ]);
            }

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Kembali ke Pengirim',
                'description' => 'Paket dikembalikan ke pengirim. Alasan: ' . $request->reason,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "RETURNED: Pengiriman #{$delivery->tracking_number} dikembalikan — {$request->reason}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil ditandai sebagai dikembalikan.',
            ]);
        });
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

            $photoPath = null;
            if ($request->hasFile('image')) {
                $file      = $request->file('image');
                $filename  = time() . '_' . $file->getClientOriginalName();
                $photoPath = $file->storeAs('uploads/proofs', $filename, 'public');
            }

            $deliveredStatus = DeliveryStatus::where('name', 'Delivered')->first();

            $delivery->update([
                'delivery_status_id' => $deliveredStatus->id,
                'image'              => $photoPath,
                'receiver_name'      => $request->receiver_name,
                'receiver_relation'  => $request->receiver_relation,
                'delivery_note'      => $request->delivery_note,
                'delivered_at'       => now(),
            ]);

            $completedStatus = ProductOrderStatus::where('name', 'Completed')->first();
            if ($delivery->order && $completedStatus) {
                $delivery->order->update([
                    'product_order_status_id' => $completedStatus->id,
                ]);
                app(\App\Services\SyncReportService::class)->syncOrderStatus($delivery->order->fresh(['status']));
            }

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => $delivery->order->shipping_address
                                 ?? $delivery->order->user->address
                                 ?? 'Lokasi Tujuan',
                'description' => "Paket diterima oleh {$request->receiver_name} ({$request->receiver_relation})",
            ]);

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
