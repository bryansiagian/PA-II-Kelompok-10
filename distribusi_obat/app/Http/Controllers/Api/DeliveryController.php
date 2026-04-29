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
    public function makeReady(Request $request, $id) {
        $request->validate([
            'product_order_type_id' => 'required|exists:product_order_types,id',
            'courier_id' => 'nullable|exists:users,id'
        ]);

        return DB::transaction(function() use ($request, $id) {
            $order = ProductOrder::findOrFail($id);

            // 1. Update Jenis Kendaraan berdasarkan pilihan Admin (Override data lama)
            $typeId = $request->product_order_type_id;
            $vehicleName = ($typeId == 2) ? 'car' : 'motorcycle'; // Konversi untuk kolom string

            $order->update([
                'product_order_type_id' => $typeId,
                'required_vehicle' => $vehicleName
            ]);

            // 2. Tentukan Status Pengiriman
            $courierId = $request->courier_id;
            $readyStatus = DeliveryStatus::where('name', 'Ready')->first();
            $claimedStatus = DeliveryStatus::where('name', 'Claimed')->first();

            // Jika kurir ditunjuk langsung, status = Claimed. Jika dikosongkan, status = Ready (Masuk Bursa)
            $finalStatusId = $courierId ? $claimedStatus->id : $readyStatus->id;

            $delivery = Delivery::create([
                'product_order_id'   => $order->id,
                'courier_id'         => $courierId,
                'delivery_status_id' => $finalStatusId,
                'tracking_number'    => 'TRK-' . strtoupper(bin2hex(random_bytes(4)))
            ]);

            // 3. Update status pesanan utama menjadi Shipping
            $orderStatusShipping = ProductOrderStatus::where('name', 'Shipping')->first();
            $order->update(['product_order_status_id' => $orderStatusShipping->id]);

            // 4. Catat di timeline tracking
            $vehicleLabel = ($typeId == 2) ? 'Mobil/Van' : 'Sepeda Motor';
            $description = $courierId
                ? "Pesanan ditugaskan langsung kepada kurir menggunakan {$vehicleLabel}."
                : "Pesanan siap dijemput di bursa tugas khusus armada {$vehicleLabel}.";

            ShipmentTracking::create([
                'delivery_id' => $delivery->id,
                'location'    => 'Gudang Pusat',
                'description' => $description
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "READY: Paket #{$id} disiapkan untuk armada {$vehicleName}"
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
    public function getCourierStats() {
        $userId = auth()->id();
        $courier = CourierDetail::where('user_id', $userId)->first();
        $myVehicle = $courier ? $courier->vehicle_type : 'motorcycle';

        $readyID = DeliveryStatus::where('name', 'Ready')->first()?->id;
        $claimedID = DeliveryStatus::where('name', 'Claimed')->first()?->id;
        $transitID = DeliveryStatus::where('name', 'In Transit')->first()?->id;
        $deliveredID = DeliveryStatus::where('name', 'Delivered')->first()?->id;

        // LOGIKA HARUS SAMA DENGAN BURSA TUGAS
        $availableQuery = Delivery::where('delivery_status_id', $readyID)
            ->whereNull('courier_id');

        $availableQuery->whereHas('order', function($q) use ($myVehicle) {
            if ($myVehicle === 'motorcycle') {
                // Kurir motor hanya melihat paket kecil
                $q->where('required_vehicle', 'motorcycle');
            } else {
                // Kurir mobil melihat paket kecil DAN besar
                $q->whereIn('required_vehicle', ['motorcycle', 'car']);
            }
        });

        return response()->json([
            'available' => $availableQuery->count(),
            'active'    => Delivery::where('courier_id', $userId)
                            ->whereIn('delivery_status_id', [$claimedID, $transitID])->count(),
            'completed' => Delivery::where('courier_id', $userId)
                            ->where('delivery_status_id', $deliveredID)->count(),
            'vehicle'   => $myVehicle // Kita kirim data kendaraan untuk UI
        ]);
    }

    /**
     * Menampilkan pengiriman yang tersedia untuk diambil kurir.
     */
    public function getAvailableDeliveries() {
        $userId = auth()->id();
        $courier = CourierDetail::where('user_id', $userId)->first();

        if (!$courier) return response()->json([]);

        $myVehicle = $courier->vehicle_type; // 'motorcycle' atau 'car'
        $readyStatusID = DeliveryStatus::where('name', 'Ready')->first()?->id;

        $query = Delivery::with(['order.user', 'order.items.product', 'status', 'order.type'])
            ->where('delivery_status_id', $readyStatusID)
            ->whereNull('courier_id');

        // LOGIKA SENIOR ENGINEER:
        $query->whereHas('order', function($q) use ($myVehicle) {
            if ($myVehicle === 'motorcycle') {
                // Kurir MOTOR: Hanya bisa lihat paket untuk Motor (Tipe ID 1)
                $q->where('product_order_type_id', 1);
            } else {
                // Kurir MOBIL: Bisa lihat SEMUA paket (Motor ID 1 & Mobil ID 2)
                // Karena mobil secara fisik bisa membawa paket kecil maupun besar
                $q->whereIn('product_order_type_id', [1, 2]);
            }
        });

        return response()->json($query->get());
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
