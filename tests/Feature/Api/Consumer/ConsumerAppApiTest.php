<?php

use App\Enums\AccessStatus;
use App\Enums\SellerInteractionEventType;
use App\Enums\SocialProvider;
use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\BusinessCategory;
use App\Models\Favorite;
use App\Models\SellerInteractionEvent;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\BusinessCategorySeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->seed(BusinessCategorySeeder::class);
});

function consumerAuth(User $user): array
{
    Sanctum::actingAs($user);

    return ['Accept' => 'application/json'];
}

it('lista categorías de negocio sin autenticación', function (): void {
    $this->getJson('/api/v1/consumer/business-categories')
        ->assertSuccessful()
        ->assertJsonCount(10, 'data');
});

it('inicia sesión social y devuelve token de comprador', function (): void {
    $this->postJson('/api/v1/consumer/auth/social', [
        'provider' => SocialProvider::Google->value,
        'provider_id' => 'google-123',
        'name' => 'Comprador Demo',
        'email' => 'buyer@example.com',
    ])->assertSuccessful()
        ->assertJsonStructure(['token', 'token_type', 'user'])
        ->assertJsonPath('user.role', UserRole::Comprador->value);

    expect(User::query()->where('email', 'buyer@example.com')->value('role'))->toBe(UserRole::Comprador);
});

it('lista solo mayoristas con acceso activo', function (): void {
    $active = User::factory()->mayorista()->create(['name' => 'Activo SA']);
    SellerProfile::query()->create([
        'user_id' => $active->id,
        'access_status' => AccessStatus::Active,
        'country' => 'México',
        'state' => 'Jalisco',
        'description' => 'Mayorista activo',
    ]);

    $pending = User::factory()->mayorista()->create(['name' => 'Pendiente SA']);
    SellerProfile::query()->create([
        'user_id' => $pending->id,
        'access_status' => AccessStatus::Pending,
        'country' => 'México',
    ]);

    $this->getJson('/api/v1/consumer/sellers?country=México')
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.name', 'Activo SA');
});

it('filtra mayoristas por categoría y estado', function (): void {
    $categoryId = BusinessCategory::query()->value('id');

    $seller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
        'business_category_id' => $categoryId,
        'country' => 'México',
        'state' => 'CDMX',
    ]);

    $this->getJson('/api/v1/consumer/sellers?business_category_id='.$categoryId.'&country=México&state=CDMX')
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 1);
});

it('muestra detalle de mayorista activo', function (): void {
    $seller = User::factory()->mayorista()->create(['name' => 'Detalle Mayorista']);
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
        'whatsapp' => '+525512345678',
        'description' => 'Descripción pública',
        'carousel_metadata' => [
            ['title' => 'Ropa hombre', 'description' => 'Tallas medianas'],
        ],
    ]);

    $this->getJson('/api/v1/consumer/sellers/'.$seller->id)
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Detalle Mayorista')
        ->assertJsonPath('data.whatsapp', '+525512345678')
        ->assertJsonPath('data.carousel_metadata.0.title', 'Ropa hombre')
        ->assertJsonStructure(['data' => ['pdf_url', 'excel_url', 'catalog_images']]);
});

it('sirve pdf en línea para vista previa y como adjunto al descargar', function (): void {
    $seller = User::factory()->mayorista()->create();
    $pdfPath = "sellers/{$seller->id}/documents/catalog.pdf";
    Storage::disk('local')->put(
        'firebase-fake/'.$pdfPath,
        '%PDF-1.4 test',
    );
    $pdfUrl = 'https://firebasestorage.googleapis.com/v0/b/test.firebasestorage.app/o/'.
        rawurlencode($pdfPath).'?alt=media&token=test';

    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
        'pdf_url' => $pdfUrl,
    ]);

    $preview = $this->get('/api/v1/consumer/sellers/'.$seller->id.'/pdf/file');
    $preview->assertSuccessful();
    expect($preview->headers->get('content-disposition'))->toContain('inline');

    $download = $this->get('/api/v1/consumer/sellers/'.$seller->id.'/pdf/file?download=1');
    $download->assertSuccessful();
    expect($download->headers->get('content-disposition'))->toContain('attachment');
});

it('expone urls de descarga de documentos en el detalle', function (): void {
    $seller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
        'pdf_url' => 'https://firebasestorage.googleapis.com/v0/b/test/o/catalog.pdf?alt=media',
        'excel_url' => 'https://firebasestorage.googleapis.com/v0/b/test/o/catalog.xlsx?alt=media',
    ]);

    $this->getJson('/api/v1/consumer/sellers/'.$seller->id)
        ->assertSuccessful()
        ->assertJsonPath('data.pdf_url', fn (string $url): bool => str_contains($url, '/sellers/'.$seller->id.'/pdf/file'))
        ->assertJsonPath('data.excel_url', fn (string $url): bool => str_contains($url, '/sellers/'.$seller->id.'/excel/file'))
        ->assertJsonPath('data.catalog_display_mode', 'pdf');
});

it('indica modo de catálogo excel o carrusel en el detalle', function (): void {
    $excelSeller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $excelSeller->id,
        'access_status' => AccessStatus::Active,
        'excel_url' => 'https://firebasestorage.googleapis.com/v0/b/test/o/catalog.xlsx?alt=media',
    ]);

    $this->getJson('/api/v1/consumer/sellers/'.$excelSeller->id)
        ->assertJsonPath('data.catalog_display_mode', 'excel');

    $carouselSeller = User::factory()->mayorista()->create();
    $profile = SellerProfile::query()->create([
        'user_id' => $carouselSeller->id,
        'access_status' => AccessStatus::Active,
        'carousel_metadata' => [['title' => 'A', 'description' => 'B']],
    ]);
    $profile->catalogImages()->create([
        'image_url' => 'https://firebasestorage.googleapis.com/v0/b/test/o/img.jpg?alt=media',
        'display_order' => 1,
    ]);

    $this->getJson('/api/v1/consumer/sellers/'.$carouselSeller->id)
        ->assertJsonPath('data.catalog_display_mode', 'carousel');
});

it('registra favorito y lista guardados', function (): void {
    $buyer = User::factory()->create();
    $seller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
    ]);

    $this->postJson('/api/v1/consumer/favorites/'.$seller->id, [], consumerAuth($buyer))
        ->assertCreated()
        ->assertJsonPath('is_favorited', true);

    $this->getJson('/api/v1/consumer/favorites', consumerAuth($buyer))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->deleteJson('/api/v1/consumer/favorites/'.$seller->id, [], consumerAuth($buyer))
        ->assertSuccessful();

    expect(Favorite::query()->count())->toBe(0);
});

it('registra eventos de interacción para métricas del mayorista', function (): void {
    $seller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Active,
    ]);

    $this->postJson('/api/v1/consumer/sellers/'.$seller->id.'/interactions', [
        'event_type' => SellerInteractionEventType::ProfileView->value,
    ])->assertCreated();

    $this->postJson('/api/v1/consumer/sellers/'.$seller->id.'/interactions', [
        'event_type' => SellerInteractionEventType::WhatsappClick->value,
    ])->assertCreated();

    $this->postJson('/api/v1/consumer/sellers/'.$seller->id.'/interactions', [
        'event_type' => SellerInteractionEventType::WebsiteClick->value,
    ])->assertCreated();

    expect(SellerInteractionEvent::query()->where('seller_user_id', $seller->id)->count())->toBe(3);
});

it('lista banners activos e incrementa clics', function (): void {
    $categoryId = BusinessCategory::query()->value('id');
    $banner = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => 'https://firebasestorage.googleapis.com/v0/b/test.firebasestorage.app/o/banners%2Ftest.jpg?alt=media&token=test',
        'sort_order' => 1,
        'is_active' => true,
        'clicks_count' => 0,
    ]);

    $this->getJson('/api/v1/consumer/banners?business_category_id='.$categoryId)
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->postJson('/api/v1/consumer/banners/'.$banner->id.'/click')
        ->assertSuccessful()
        ->assertJsonPath('clicks_count', 1);
});

it('rechaza favoritos de un mayorista no activo', function (): void {
    $buyer = User::factory()->create();
    $seller = User::factory()->mayorista()->create();
    SellerProfile::query()->create([
        'user_id' => $seller->id,
        'access_status' => AccessStatus::Pending,
    ]);

    $this->postJson('/api/v1/consumer/favorites/'.$seller->id, [], consumerAuth($buyer))
        ->assertUnprocessable();
});

it('rechaza token de mayorista en rutas de comprador', function (): void {
    $seller = User::factory()->mayorista()->create([
        'password' => Hash::make('secret12'),
    ]);
    SellerProfile::query()->create(['user_id' => $seller->id]);

    $token = $seller->createToken('seller-app')->plainTextToken;

    $this->getJson('/api/v1/consumer/me', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertForbidden();
});
