<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json(Vehicle::orderBy('created_at', 'desc')->get());
    }

    // Dipakai di openShipModal — kembalikan juga status busy tiap kendaraan
    public function indexWithStatus()
    {
        $activeStatusIds = DeliveryStatus::whereIn('name', ['Claimed', 'In Transit'])
            ->pluck('id');

        $busyVehicleIds = Delivery::whereIn('delivery_status_id', $activeStatusIds)
            ->whereNotNull('vehicle_id')
            ->pluck('vehicle_id')
            ->toArray();

        $vehicles = Vehicle::where('active', true)
            ->orderBy('brand')
            ->get()
            ->map(fn($v) => array_merge($v->toArray(), [
                'is_busy' => in_array($v->id, $busyVehicleIds),
            ]));

        return response()->json($vehicles);
    }

    // Dipakai di openShipModal — kurir yang sedang aktif di-flag busy
    public function couriersWithStatus()
    {
        $activeStatusIds = DeliveryStatus::whereIn('name', ['Claimed', 'In Transit'])
            ->pluck('id');

        $busyCourierIds = Delivery::whereIn('delivery_status_id', $activeStatusIds)
            ->whereNotNull('courier_id')
            ->pluck('courier_id')
            ->toArray();

        $couriers = \App\Models\User::role('courier')
            ->where('status', 1)
            ->get()
            ->map(fn($u) => [
                'id'      => $u->id,
                'name'    => $u->name,
                'is_busy' => in_array($u->id, $busyCourierIds),
            ]);

        return response()->json($couriers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:motorcycle,car',
            'subtype'      => 'required|string|max:50',
            'brand'        => 'required|string|max:50',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'color'        => 'required|string|max:30',
        ]);

        $vehicle = Vehicle::create([
            'type'         => $request->type,
            'subtype'      => $request->subtype,
            'brand'        => $request->brand,
            'plate_number' => strtoupper($request->plate_number),
            'color'        => $request->color,
            'active'       => true,
        ]);

        return response()->json($vehicle, 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'type'         => 'required|in:motorcycle,car',
            'subtype'      => 'required|string|max:50',
            'brand'        => 'required|string|max:50',
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $id,
            'color'        => 'required|string|max:30',
            'active'       => 'boolean',
        ]);

        $vehicle->update([
            'type'         => $request->type,
            'subtype'      => $request->subtype,
            'brand'        => $request->brand,
            'plate_number' => strtoupper($request->plate_number),
            'color'        => $request->color,
            'active'       => $request->active ?? $vehicle->active,
        ]);

        return response()->json($vehicle);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Cek apakah kendaraan sedang aktif dipakai
        $activeStatusIds = DeliveryStatus::whereIn('name', ['Claimed', 'In Transit'])
            ->pluck('id');

        $isBusy = Delivery::where('vehicle_id', $id)
            ->whereIn('delivery_status_id', $activeStatusIds)
            ->exists();

        if ($isBusy) {
            return response()->json([
                'message' => 'Kendaraan sedang digunakan, tidak bisa dihapus.'
            ], 422);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Kendaraan berhasil dihapus']);
    }
}
