<?php

namespace App\Support\Geo;

class GeoDistance
{
    public const EARTH_RADIUS_KM = 6371;

    /**
     * SQL expression for Haversine distance in kilometers.
     * Bindings order: latitude, longitude, latitude (of reference point).
     */
    public static function haversineExpression(string $latColumn, string $lngColumn): string
    {
        return sprintf(
            '%d * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(%s)) * cos(radians(%s) - radians(?)) + sin(radians(?)) * sin(radians(%s)))))',
            self::EARTH_RADIUS_KM,
            $latColumn,
            $lngColumn,
            $latColumn,
        );
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    public static function bindings(float $latitude, float $longitude): array
    {
        return [$latitude, $longitude, $latitude];
    }

    public static function kilometersBetween(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }
}
