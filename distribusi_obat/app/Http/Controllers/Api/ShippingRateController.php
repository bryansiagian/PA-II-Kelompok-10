<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShippingRateService;
use Illuminate\Http\Request;
use App\Models\ShippingRate;

class ShippingRateController extends Controller
{
    public function calculate(Request $request, ShippingRateService $service)
    {
        $request->validate([
            'village_id'  => 'required|string',
            'district_id' => 'required|string',
            'regency_id'  => 'required|string',
            'request_type'=> 'required|in:delivery,self_pickup',
        ]);

        if ($request->request_type === 'self_pickup') {
            return response()->json(['rate' => 0, 'tier' => 'self_pickup']);
        }

        $rate = $service->calculate(
            $request->village_id,
            $request->district_id,
            $request->regency_id,
        );

        $tier = $service->resolveTier(
            $request->village_id,
            $request->district_id,
            $request->regency_id,
        );

        return response()->json(['rate' => $rate, 'tier' => $tier]);
    }

    public function index()
    {
        return response()->json(ShippingRate::orderBy('tier')->orderBy('regency_name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'tier'         => 'required|in:same_village,same_district,same_regency,other_regency',
            'regency_id'   => 'nullable|string',
            'regency_name' => 'nullable|string',
            'rate'         => 'required|integer|min:0',
        ]);

        $rate = ShippingRate::updateOrCreate(
            ['tier' => $request->tier, 'regency_id' => $request->regency_id],
            ['regency_name' => $request->regency_name, 'rate' => $request->rate]
        );

        return response()->json($rate, 201);
    }

    public function update(Request $request, $id)
    {
        $rate = ShippingRate::findOrFail($id);
        $rate->update($request->only(['rate', 'regency_name']));
        return response()->json($rate);
    }

    public function destroy($id)
    {
        ShippingRate::findOrFail($id)->delete();
        return response()->json(['message' => 'Tarif dihapus']);
    }
}
