<?php

use App\Support\Geo\GeoDistance;

it('calculates haversine distance between two coordinates', function (): void {
    $distance = GeoDistance::kilometersBetween(
        19.4326,
        -99.1332,
        19.3900,
        -99.1600,
    );

    expect($distance)->toBeGreaterThan(4.5)
        ->and($distance)->toBeLessThan(6.5);
});

it('returns zero distance for identical coordinates', function (): void {
    $distance = GeoDistance::kilometersBetween(
        19.4326,
        -99.1332,
        19.4326,
        -99.1332,
    );

    expect($distance)->toBeLessThan(0.001);
});

it('builds haversine sql with three placeholders', function (): void {
    $sql = GeoDistance::haversineExpression('seller_profiles.latitude', 'seller_profiles.longitude');

    expect($sql)->toContain('seller_profiles.latitude')
        ->and($sql)->toContain('seller_profiles.longitude')
        ->and(substr_count($sql, '?'))->toBe(3);
});
