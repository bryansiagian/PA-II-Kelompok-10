<?php

namespace App\Services;

use App\Models\ShippingRate;

class ShippingRateService
{
    // ID gudang — Sitoluama, Laguboti, Toba Samosir
    const WAREHOUSE_REGENCY_ID  = '1206';
    const WAREHOUSE_DISTRICT_ID = '1206040';
    const WAREHOUSE_VILLAGE_ID  = '1206040018';

    public function calculate(string $villageId, string $districtId, string $regencyId): int
    {
        $tier = $this->resolveTier($villageId, $districtId, $regencyId);

        if ($tier === 'same_village') {
            $rate = ShippingRate::where('tier', 'same_village')->first();
        } elseif ($tier === 'same_district') {
            $rate = ShippingRate::where('tier', 'same_district')->first();
        } elseif ($tier === 'same_regency') {
            $rate = ShippingRate::where('tier', 'same_regency')->first();
        } else {
            // other_regency — cari tarif spesifik per kabupaten
            $rate = ShippingRate::where('tier', 'other_regency')
                ->where('regency_id', $regencyId)
                ->first();

            // fallback: kalau belum di-set, ambil tarif other_regency tanpa regency_id spesifik
            if (!$rate) {
                $rate = ShippingRate::where('tier', 'other_regency')
                    ->whereNull('regency_id')
                    ->first();
            }
        }

        return $rate ? (int) $rate->rate : 0;
    }

    public function resolveTier(string $villageId, string $districtId, string $regencyId): string
    {
        if ($villageId  === self::WAREHOUSE_VILLAGE_ID)  return 'same_village';
        if ($districtId === self::WAREHOUSE_DISTRICT_ID) return 'same_district';
        if ($regencyId  === self::WAREHOUSE_REGENCY_ID)  return 'same_regency';
        return 'other_regency';
    }
}
