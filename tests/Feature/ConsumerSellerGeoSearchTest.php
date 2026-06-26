<?php

use App\Enums\AccessStatus;
use App\Models\SellerProfile;
use App\Models\User;

it('filters sellers within radius and sorts by distance', function (): void {
    $near = User::factory()->mayorista()->create(['name' => 'Dr. Cerca']);
    SellerProfile::query()->create([
        'user_id' => $near->id,
        'access_status' => AccessStatus::Active,
        'latitude' => 19.3900,
        'longitude' => -99.1600,
        'municipality' => 'Benito Juárez',
    ]);

    $far = User::factory()->mayorista()->create(['name' => 'Dr. Lejos']);
    SellerProfile::query()->create([
        'user_id' => $far->id,
        'access_status' => AccessStatus::Active,
        'latitude' => 25.6866,
        'longitude' => -100.3161,
        'municipality' => 'Monterrey',
    ]);

    $noCoords = User::factory()->mayorista()->create(['name' => 'Dr. Sin coords']);
    SellerProfile::query()->create([
        'user_id' => $noCoords->id,
        'access_status' => AccessStatus::Active,
    ]);

    $response = $this->getJson('/api/v1/consumer/sellers?latitude=19.4326&longitude=-99.1332&radius_km=20');

    $response->assertSuccessful()
        ->assertJsonPath('meta.geo.sorted_by', 'distance')
        ->assertJsonPath('meta.geo.radius_km', 20);

    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toContain('Dr. Cerca')
        ->and($names)->not->toContain('Dr. Lejos')
        ->and($names)->not->toContain('Dr. Sin coords')
        ->and($response->json('data.0.name'))->toBe('Dr. Cerca')
        ->and($response->json('data.0.distance_km'))->toBeFloat();
});

it('filters sellers by cdmx region and municipality', function (): void {
    $cdmx = User::factory()->mayorista()->create(['name' => 'Dr. CDMX']);
    SellerProfile::query()->create([
        'user_id' => $cdmx->id,
        'access_status' => AccessStatus::Active,
        'state' => 'Ciudad de México',
        'municipality' => 'Benito Juárez',
    ]);

    $edomex = User::factory()->mayorista()->create(['name' => 'Dr. Edomex']);
    SellerProfile::query()->create([
        'user_id' => $edomex->id,
        'access_status' => AccessStatus::Active,
        'state' => 'Estado de México',
        'municipality' => 'Ecatepec de Morelos',
    ]);

    $response = $this->getJson('/api/v1/consumer/sellers?region=cdmx&municipality=Benito Juárez');

    $response->assertSuccessful();

    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toContain('Dr. CDMX')
        ->and($names)->not->toContain('Dr. Edomex');
});

it('lists geo regions and municipalities', function (): void {
    $this->getJson('/api/v1/consumer/filters/regions')
        ->assertSuccessful()
        ->assertJsonFragment(['key' => 'cdmx'])
        ->assertJsonFragment(['key' => 'edo_mex']);

    $this->getJson('/api/v1/consumer/filters/municipalities?region=cdmx')
        ->assertSuccessful()
        ->assertJsonFragment('Benito Juárez');
});
