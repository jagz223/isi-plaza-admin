<?php

use App\Enums\AccessStatus;
use App\Enums\SellerInteractionEventType;
use App\Enums\UserRole;
use App\Models\BusinessCategory;
use App\Models\CatalogImage;
use App\Models\SellerInteractionEvent;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\BusinessCategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(BusinessCategorySeeder::class);
});

function sellerAuth(User $user): array
{
    Sanctum::actingAs($user);

    return ['Accept' => 'application/json'];
}

it('registra un mayorista y devuelve token', function (): void {
    $response = $this->postJson('/api/v1/seller/register', [
        'name' => 'Mayorista Demo',
        'email' => 'seller@example.com',
        'password' => 'secret12',
        'password_confirmation' => 'secret12',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'token_type', 'user'])
        ->assertJsonPath('user.role', UserRole::Mayorista->value)
        ->assertJsonPath('user.seller_profile.access_status', AccessStatus::Pending->value);

    expect(User::query()->where('email', 'seller@example.com')->exists())->toBeTrue();
});

it('inicia sesión como mayorista', function (): void {
    $user = User::factory()->mayorista()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('secret12'),
    ]);
    SellerProfile::query()->create(['user_id' => $user->id, 'access_status' => AccessStatus::Pending]);

    $this->postJson('/api/v1/seller/login', [
        'email' => 'login@example.com',
        'password' => 'secret12',
    ])->assertSuccessful()
        ->assertJsonStructure(['token', 'user']);
});

it('muestra pantalla de suscripción con acceso pendiente', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create(['user_id' => $user->id, 'access_status' => AccessStatus::Pending]);

    $this->getJson('/api/v1/seller/subscription', sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonPath('can_access_app', false)
        ->assertJsonPath('access_status', 'pending')
        ->assertJsonStructure(['subscription_price_label', 'whatsapp_payment_url']);
});

it('bloquea perfil hasta que el admin active el acceso', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create(['user_id' => $user->id, 'access_status' => AccessStatus::Pending]);

    $this->getJson('/api/v1/seller/profile', sellerAuth($user))->assertForbidden();
});

it('permite dar de alta el perfil con acceso activo', function (): void {
    $user = User::factory()->mayorista()->create();
    $profile = SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
        'subscription_expires_at' => now()->addDays(30),
    ]);

    $categoryId = BusinessCategory::query()->value('id');

    $this->patchJson('/api/v1/seller/profile', [
        'business_category_id' => $categoryId,
        'description' => 'Perfil de prueba',
        'country' => 'México',
        'state' => ['Jalisco'],
        'whatsapp' => '+525512345678',
    ], sellerAuth($user))->assertSuccessful()
        ->assertJsonPath('data.seller_profile.description', 'Perfil de prueba');

    expect($profile->fresh()->description)->toBe('Perfil de prueba');
});

it('persiste perfil con PATCH json y GET profile devuelve los mismos valores', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
    ]);

    $categoryId = BusinessCategory::query()->value('id');

    $payload = [
        'business_category_id' => $categoryId,
        'description' => 'Descripción guardada',
        'country' => 'México',
        'state' => ['Nuevo León'],
    ];

    $this->patchJson('/api/v1/seller/profile', $payload, sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonPath('data.seller_profile.country', 'México');

    $this->getJson('/api/v1/seller/profile', sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonPath('data.seller_profile.description', 'Descripción guardada')
        ->assertJsonPath('data.seller_profile.state', ['Nuevo León'])
        ->assertJsonPath('data.seller_profile.business_category.id', $categoryId);

    $row = SellerProfile::query()->where('user_id', $user->id)->first();
    expect($row)->not->toBeNull()
        ->and($row->description)->toBe('Descripción guardada')
        ->and($row->country)->toBe('México');
});

it('sube imagen de catálogo con multipart y persiste en catalog_images', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
    ]);

    $file = UploadedFile::fake()->image('catalog.jpg', 400, 400);

    $this->post('/api/v1/seller/catalog-images', [
        'image' => $file,
        'display_order' => 2,
    ], sellerAuth($user))
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'image_url', 'display_order']])
        ->assertJsonPath('data.display_order', 2);

    $stored = CatalogImage::query()->first();
    expect($stored)->not->toBeNull()
        ->and(CatalogImage::query()->count())->toBe(1)
        ->and($stored->image_url)->toStartWith('https://firebasestorage.googleapis.com/');

    $imageId = $stored->id;
    $filePath = "/api/v1/seller/catalog-images/{$imageId}/file";

    $this->getJson('/api/v1/seller/catalog-images', sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonPath('data.0.image_url', fn (string $url): bool => str_contains($url, $filePath));

    $this->get($filePath, sellerAuth($user))
        ->assertSuccessful()
        ->assertHeader('content-type', 'image/jpeg');
});

it('devuelve métricas del último mes', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create(['user_id' => $user->id, 'access_status' => AccessStatus::Active]);

    SellerInteractionEvent::query()->create([
        'seller_user_id' => $user->id,
        'event_type' => SellerInteractionEventType::ProfileView,
        'created_at' => now()->subDays(3),
    ]);
    SellerInteractionEvent::query()->create([
        'seller_user_id' => $user->id,
        'event_type' => SellerInteractionEventType::WhatsappClick,
        'created_at' => now()->subDays(5),
    ]);

    $this->getJson('/api/v1/seller/metrics', sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonPath('profile_views_count', 1)
        ->assertJsonPath('whatsapp_clicks_count', 1);
});

it('muestra ajustes con fecha de suscripción', function (): void {
    $user = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $user->id,
        'access_status' => AccessStatus::Active,
        'subscription_expires_at' => now()->addDays(10),
    ]);

    $this->getJson('/api/v1/seller/settings', sellerAuth($user))
        ->assertSuccessful()
        ->assertJsonStructure([
            'subscription_expires_at',
            'subscription_expires_at_formatted',
            'promotion_whatsapp_url',
        ]);
});

it('rechaza token de panel admin en rutas seller', function (): void {
    $this->getJson('/api/v1/seller/me', [
        'Authorization' => 'Bearer paneltoken12',
        'Accept' => 'application/json',
    ])->assertUnauthorized();
});
