<?php

namespace App\Support;

/** Straight-line distance on the Earth's surface (Haversine formula). Used by matching (M2.3) and the map (M2.4). */
final class Distance
{
    private const EARTH_RADIUS_KM = 6371;

    public static function km(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1, sqrt($a)));
    }
}
