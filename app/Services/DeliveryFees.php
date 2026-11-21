<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\Region;
use App\Models\Setting;

/** Works out the delivery fee for a place. The fee always comes from here, never from what the browser sends. */
class DeliveryFees
{
    /** The region that counts as "within Accra". */
    public const ACCRA_REGION = 'Greater Accra';

    public static function isAccra(?Region $region): bool
    {
        return $region !== null && $region->name === self::ACCRA_REGION;
    }

    /** Extra charge for taking an Express order to the station when it is going outside Accra. */
    public static function expressStationFee(): float
    {
        return (float) Setting::get('express_station_fee', '30');
    }

    /**
     * The delivery fee for a way of receiving the order and a place. Pick up is free. Door and Express use the fee of the
     * chosen town (or the region's), and Express outside Accra adds the station transport charge.
     *
     * @return array{fee: float, base: float, station: float}
     */
    public static function quote(string $service, ?Region $region, ?DeliveryZone $zone): array
    {
        if ($service === 'pickup') {
            return ['fee' => 0.0, 'base' => 0.0, 'station' => 0.0];
        }
        $base = self::feeFor($region, $zone);
        $station = ($service === 'express' && $region && ! self::isAccra($region)) ? self::expressStationFee() : 0.0;

        return ['fee' => round($base + $station, 2), 'base' => $base, 'station' => $station];
    }

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
            'id' => $r->id, 'name' => $r->name, 'accra' => $r->name === self::ACCRA_REGION,
            'fee' => $r->default_fee !== null ? (float) $r->default_fee : (float) Setting::get('default_delivery_fee', '0'),
            'zones' => $r->zones->map(fn ($z) => ['id' => $z->id, 'name' => $z->name, 'fee' => $z->fee !== null ? (float) $z->fee : self::feeFor($r, null)])->values()->all(),
        ])->values()->all();
    }
}
