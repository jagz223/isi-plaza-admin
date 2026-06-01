<?php

use App\Models\AdminToken;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

beforeEach(function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Prueba',
        'is_active' => true,
    ]);
});

function adminAuthHeaders(): array
{
    return [
        'Authorization' => 'Bearer paneltoken12',
        'Accept' => 'application/json',
    ];
}

it('rechaza la api admin sin token', function (): void {
    /** @var TestCase $this */
    $this->getJson('/api/v1/admin/stats')->assertUnauthorized();
});

it('devuelve estadísticas con token válido', function (): void {
    /** @var TestCase $this */
    User::factory()->count(2)->create();
    User::factory()->mayorista()->count(3)->create();

    $this->getJson('/api/v1/admin/stats', adminAuthHeaders())
        ->assertSuccessful()
        ->assertJsonPath('buyers_count', 2)
        ->assertJsonPath('sellers_count', 3)
        ->assertJsonPath('active_admin_tokens_count', 1);
});

it('lista compradores y permite eliminar uno', function (): void {
    /** @var TestCase $this */
    $buyer = User::factory()->create();

    $this->getJson('/api/v1/admin/buyers', adminAuthHeaders())
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $buyer->id);

    $this->deleteJson('/api/v1/admin/buyers/'.$buyer->id, [], adminAuthHeaders())
        ->assertNoContent();

    expect(User::query()->find($buyer->id))->toBeNull();
});

it('actualiza el perfil de un vendedor', function (): void {
    /** @var TestCase $this */
    $seller = User::factory()->mayorista()->create();

    $this->patchJson(
        '/api/v1/admin/sellers/'.$seller->id.'/profile',
        [
            'access_status' => 'active',
            'is_verified' => true,
            'has_paid_promotion' => false,
        ],
        adminAuthHeaders()
    )->assertSuccessful()
        ->assertJsonPath('data.seller_profile.access_status', 'active')
        ->assertJsonPath('data.seller_profile.is_verified', true);
});

it('crea un banner con imagen', function (): void {
    /** @var TestCase $this */
    $file = UploadedFile::fake()->image('banner.jpg', 800, 200);

    $this->post(
        '/api/v1/admin/banners',
        [
            'image' => $file,
            'sort_order' => 1,
            'is_active' => true,
        ],
        adminAuthHeaders()
    )->assertCreated();

    $banner = Banner::query()->first();
    expect($banner)->not->toBeNull()
        ->and(Banner::query()->count())->toBe(1)
        ->and($banner->image_url)->toStartWith('https://firebasestorage.googleapis.com/');
});

it('crea un token de panel y devuelve el texto plano una vez', function (): void {
    /** @var TestCase $this */
    $response = $this->postJson(
        '/api/v1/admin/tokens',
        ['description' => 'Equipo soporte'],
        adminAuthHeaders()
    )->assertCreated();

    $plain = $response->json('plain_token');
    expect($plain)->toBeString()->and(strlen($plain))->toBeGreaterThanOrEqual(9)->and(strlen($plain))->toBeLessThanOrEqual(15);

    $this->getJson('/api/v1/admin/stats', [
        'Authorization' => 'Bearer '.$plain,
        'Accept' => 'application/json',
    ])->assertSuccessful();
});
