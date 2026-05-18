<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\ProductOrder; // Ganti dari DrugRequest
use App\Models\ProductOrderStatus;
use App\Models\DeliveryStatus; // Tabel lookup baru
use App\Models\ShipmentTracking;
use App\Models\AuditLog;
use App\Models\CourierDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller {

    /**
     * Mendapatkan data tracking berdasarkan ID Delivery.
     */
    public function getTracking($id) {
        // Relasi disesuaikan: order.user dan status
        return response()->json(Delivery::with([
            'order.user',
            'status',
            'courier',
            'trackings' => fn($q) => $q->latest()
        ])->findOrFail($id));
    }

    /**
     * Mengubah pesanan menjadi siap kirim (Ready for Delivery).
     * Dipanggil oleh Operator Gudang.
     */
    public function makeReady(Request $request, $id)
    {
        $request->validate([
            'vehicle_id'    => 'required|exists:vehicles,id',
            'courier_id'    => 'nullable|exists:users,id',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $order = ProductOrder::findOrFail($id);
            $vehicle = \App\Models\Vehicle::findOrFail($request->vehicle_id);

            $readyStatus   = DeliveryStatus::where('name', 'Ready')->first();
            $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();

            $courierId   = $request->courier_id;
            $finalStatus = $courierId ? $claimedStatus->id : $readyStatus->id;

            $delivery = Delivery::create([
                'product_order_id'   => $order->id,
                'courier_id'         => $courierId,
                'vehicle_id'         => $request->vehicle_id,
                'delivery_status_id' => $finalStatus,
                'tracking_number'    => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
            ]);

            $orderStatusShipping = ProductOrderStatus::where('name', 'Shipping')->first();
            $order->update(['product_order_status_id' => $orderStatusShipping->id]);

            $vehicleDesc = "{$vehicle->brand} {$vehicle->subtype} ({$vehicle->plate_number}) warna {$vehicle->color}";
            $description = $courierId
                ? "Pesanan ditugaskan kepada kurir menggunakan {$vehicleDesc}."
                : "Pesanan siap dijemput menggunakan {$vehicleDesc}.";

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Gudang Pusat',
                'description' => $description,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action'  => "READY: Paket #{$id} disiapkan dengan kendaraan {$vehicle->plate_number}",
            ]);

            return response()->json(['message' => 'Resi diterbitkan. Pesanan siap didistribusikan.']);
        });
    }

    /**
     * Kurir mengambil tugas pengiriman (Claim).
     */
    public function claim($id) {
        return DB::transaction(function() use ($id) {
            $delivery = Delivery::lockForUpdate()->findOrFail($id);
            if ($delivery->courier_id) return response()->json(['message' => 'Sudah diambil kurir lain'], 422);

            $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();
            $delivery->update([
                'courier_id' => auth()->id(),
                'delivery_status_id' => $claimedStatus->id
            ]);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Gudang Pusat',
                'description' => 'Kurir ' . auth()->user()->name . ' telah mengonfirmasi pengambilan paket.'
            ]);

            return response()->json(['message' => 'Tugas berhasil diambil']);
        });
    }

    /**
     * Kurir memulai perjalanan.
     */
    public function startShipping($id) {
        $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();

        $inTransitStatus = DeliveryStatus::where('name', 'In Transit')->first();
        $delivery->update(['delivery_status_id' => $inTransitStatus->id]);

        ShipmentTracking::create([
            'delivery_id' => $delivery->id,
            'location'    => 'Dalam Perjalanan',
            'description' => 'Kurir sedang menuju lokasi tujuan.'
        ]);

        return response()->json(['message' => 'Status: Dalam perjalanan']);
    }

    /**
     * Konfirmasi paket sampai di tujuan (Delivered).
     */
    public function complete(Request $request, $id) {
        $request->validate([
            'image' => 'required|image|max:2048',
            'receiver_name' => 'required|string',
            'receiver_relation' => 'required|string'
        ]);

        return DB::transaction(function() use ($request, $id) {
            $delivery = Delivery::where('id', $id)->where('courier_id', auth()->id())->firstOrFail();
            $path = $request->file('image')->store('proofs', 'public');

            $deliveredStatus = DeliveryStatus::where('name', 'Delivered')->first();
            $delivery->update([
                'delivery_status_id' => $deliveredStatus->id,
                'image'             => $path, // Field ganti dari proof_image sesuai DBML
                'receiver_name'     => $request->receiver_name,
                'receiver_relation' => $request->receiver_relation,
                'delivered_at'      => now()
            ]);

            // Update status pesanan utama menjadi Completed
            $orderStatusCompleted = ProductOrderStatus::where('name', 'Completed')->first();
            $delivery->order->update(['product_order_status_id' => $orderStatusCompleted->id]);

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Lokasi Tujuan',
                'description' => "Paket diterima oleh {$request->receiver_name} ({$request->receiver_relation})"
            ]);

            AuditLog::create(['user_id' => auth()->id(), 'action' => "DELIVERED: Pengiriman selesai #{$delivery->tracking_number}"]);
            return response()->json(['message' => 'Pengiriman Selesai!']);
        });
    }

    /**
     * Statistik untuk Dashboard Kurir.
     */
    public function getCourierStats()
    {
        $userId = auth()->id();

        $readyID     = DeliveryStatus::where('name', 'Ready')->first()?->id;
        $claimedID   = DeliveryStatus::where('name', 'Claimed')->first()?->id;
        $transitID   = DeliveryStatus::where('name', 'In Transit')->first()?->id;
        $deliveredID = DeliveryStatus::where('name', 'Delivered')->first()?->id;

        return response()->json([
            'available' => Delivery::where('delivery_status_id', $readyID)
                            ->whereNull('courier_id')->count(),
            'active'    => Delivery::where('courier_id', $userId)
                            ->whereIn('delivery_status_id', [$claimedID, $transitID])->count(),
            'completed' => Delivery::where('courier_id', $userId)
                            ->where('delivery_status_id', $deliveredID)->count(),
        ]);
    }

    public function getAvailableDeliveries()
    {
        $readyStatusID = DeliveryStatus::where('name', 'Ready')->first()?->id;

        return response()->json(
            Delivery::with(['order.user', 'order.items.product', 'status', 'vehicle'])
                ->where('delivery_status_id', $readyStatusID)
                ->whereNull('courier_id')
                ->get()
        );
    }

    /**
     * Menampilkan tugas pengiriman yang sedang berjalan.
     */
    public function getActiveDeliveries() {
        $claimedID = DeliveryStatus::where('name', 'Claimed')->first()?->id;
        $transitID = DeliveryStatus::where('name', 'In Transit')->first()?->id;

        // TAMBAHKAN 'status' di dalam with()
        return Delivery::with(['order.user', 'order.items.product', 'status'])
            ->where('courier_id', auth()->id())
            ->whereIn('delivery_status_id', [$claimedID, $transitID])
            ->get();
    }

    /**
     * Riwayat pengiriman kurir.
     */
    public function getCourierHistory() {
        try {
            $deliveredID = DeliveryStatus::where('name', 'Delivered')->first()?->id;

            $history = Delivery::with(['order.user', 'order.items.product'])
                ->where('courier_id', auth()->id())
                ->where('delivery_status_id', $deliveredID)
                ->latest('delivered_at')
                ->get();

            return response()->json($history, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memuat riwayat: ' . $e->getMessage()], 500);
        }
    }
}
