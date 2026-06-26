<?php

use App\Enums\AccessStatus;
use App\Models\DoctorService;
use App\Models\SellerProfile;
use App\Models\Treatment;
use App\Models\TreatmentSection;
use App\Models\User;
use Database\Seeders\TreatmentSectionSeeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(TreatmentSectionSeeder::class);
});

it('lists treatment sections for consumers', function (): void {
    $response = $this->getJson('/api/v1/consumer/treatments');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Ortodoncia')
        ->assertJsonStructure([
            'data' => [
                ['id', 'name', 'slug', 'treatments' => [['id', 'name']]],
            ],
        ]);
});

it('filters sellers by treatment id', function (): void {
    $section = TreatmentSection::query()->where('slug', 'ortodoncia')->firstOrFail();
    $treatment = Treatment::query()
        ->where('treatment_section_id', $section->id)
        ->where('slug', 'brackets-metalicos')
        ->firstOrFail();

    $withService = User::factory()->mayorista()->create(['name' => 'Dr. Con servicio']);
    $profile = SellerProfile::query()->create([
        'user_id' => $withService->id,
        'access_status' => AccessStatus::Active,
    ]);
    DoctorService::query()->create([
        'seller_profile_id' => $profile->id,
        'treatment_id' => $treatment->id,
        'price' => 2000,
    ]);

    $withoutService = User::factory()->mayorista()->create(['name' => 'Dr. Sin servicio']);
    SellerProfile::query()->create([
        'user_id' => $withoutService->id,
        'access_status' => AccessStatus::Active,
    ]);

    $response = $this->getJson('/api/v1/consumer/sellers?treatment_id='.$treatment->id);

    $response->assertSuccessful();
    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toContain('Dr. Con servicio')
        ->and($names)->not->toContain('Dr. Sin servicio');
});

it('syncs doctor services for an active seller', function (): void {
    $user = User::factory()->mayorista()->create([
        'password' => Hash::make('secret12'),
    ]);
    $profile = SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
    ]);

    $treatment = Treatment::query()->firstOrFail();

    Sanctum::actingAs($user);

    $this->putJson('/api/v1/seller/doctor-services', [
        'services' => [
            ['treatment_id' => $treatment->id, 'price' => 1500.50],
        ],
    ])->assertSuccessful()
        ->assertJsonPath('seller_profile.doctor_services.0.treatment_id', $treatment->id);

    expect(DoctorService::query()->where('seller_profile_id', $profile->id)->count())->toBe(1);
});

it('updates dental profile fields for seller', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
    ]);

    Sanctum::actingAs($user);

    $this->patchJson('/api/v1/seller/profile', [
        'professional_license' => '1234567',
        'phone' => '+525512345678',
        'address' => 'Av. Insurgentes Sur 742, CDMX',
        'municipality' => 'Benito Juárez',
        'latitude' => 19.391,
        'longitude' => -99.168,
    ])->assertSuccessful()
        ->assertJsonPath('seller_profile.professional_license', '1234567')
        ->assertJsonPath('seller_profile.municipality', 'Benito Juárez');
});
