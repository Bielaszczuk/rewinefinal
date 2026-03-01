<?php

namespace App\Utils;

class GeoUtils
{
    private const EARTH_RADIUS_KM = 6371.0;

    public static function haversineDistance(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    public static function boundingBox(float $lat, float $lon, float $radiusKm): array
    {
        $latDelta = $radiusKm / self::EARTH_RADIUS_KM * (180 / M_PI);
        $lonDelta = $radiusKm / (self::EARTH_RADIUS_KM * cos(deg2rad($lat))) * (180 / M_PI);

        return [
            'minLat' => $lat - $latDelta,
            'maxLat' => $lat + $latDelta,
            'minLon' => $lon - $lonDelta,
            'maxLon' => $lon + $lonDelta,
        ];
    }
}
