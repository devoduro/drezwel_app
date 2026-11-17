<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Region;
use App\Models\Setting;

/** Works out the delivery fee for a place. The fee always comes from here, never from what the browser sends. */
class DeliveryFees
{
    /** Fee for a chosen region and (optionally) town. Falls back to the region's fee, then the shop-wide default. */
    public static function feeFor(?Region $region, ?DeliveryZone $zone): float
    {
        if ($zone && $zone->fee !== null) {
            return (float) $zone->fee;
        }
        if ($region && $region->default_fee !== null) {
            return (float) $region->default_fee;
        }

        return (float) Setting::get('default_delivery_fee', '0');
    }

    /** Regions with the towns customers can pick, for the checkout page. */
    public static function catalogue(): array
    {
        return Region::with(['zones' => fn ($q) => $q->orderable()])->orderBy('sort_order')->get()->map(fn (Region $r) => [
            'id' => $r->id, 'name' => $r->name,
            'fee' => $r->default_fee !== null ? (float) $r->default_fee : (float) Setting::get('default_delivery_fee', '0'),
            'zones' => $r->zones->map(fn ($z) => ['id' => $z->id, 'name' => $z->name, 'fee' => (float) $z->fee])->values()->all(),
        ])->values()->all();
    }
}
