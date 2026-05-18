<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json(Vehicle::where('active', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:motorcycle,car',
            'subtype'      => 'required|string',
            'brand'        => 'required|string',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'color'        => 'required|string',
        ]);

        $vehicle = Vehicle::create($request->all());
        return response()->json(['message' => 'Kendaraan berhasil ditambahkan', 'vehicle' => $vehicle], 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'type'         => 'required|in:motorcycle,car',
            'subtype'      => 'required|string',
            'brand'        => 'required|string',
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $id,
            'color'        => 'required|string',
        ]);

        $vehicle->update($request->all());
        return response()->json(['message' => 'Kendaraan berhasil diupdate']);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update(['active' => false]); // soft delete
        return response()->json(['message' => 'Kendaraan berhasil dihapus']);
    }
}
