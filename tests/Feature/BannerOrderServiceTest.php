<?php

use App\Models\Banner;
use App\Models\BusinessCategory;
use App\Services\Banner\BannerOrderService;
use Database\Seeders\BusinessCategorySeeder;

beforeEach(function (): void {
    $this->seed(BusinessCategorySeeder::class);
    $this->service = app(BannerOrderService::class);
});

it('intercambia orden al bajar un banner', function (): void {
    $categoryId = BusinessCategory::query()->value('id');
    $url = 'https://firebasestorage.googleapis.com/v0/b/test/o/b.jpg?alt=media';

    $b1 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $b2 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $b3 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->service->move($b1, 'down');

    expect($b1->fresh()->sort_order)->toBe(2)
        ->and($b2->fresh()->sort_order)->toBe(1)
        ->and($b3->fresh()->sort_order)->toBe(3);
});

it('intercambia orden al subir un banner', function (): void {
    $categoryId = BusinessCategory::query()->value('id');
    $url = 'https://firebasestorage.googleapis.com/v0/b/test/o/b.jpg?alt=media';

    $b1 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $b2 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $b3 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->service->move($b3, 'up');

    expect($b1->fresh()->sort_order)->toBe(1)
        ->and($b2->fresh()->sort_order)->toBe(3)
        ->and($b3->fresh()->sort_order)->toBe(2);
});

it('compacta orden al eliminar el primer banner del rubro', function (): void {
    $categoryId = BusinessCategory::query()->value('id');
    $url = 'https://firebasestorage.googleapis.com/v0/b/test/o/b.jpg?alt=media';

    $b1 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $b2 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $b3 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->service->deleteAndCompact($b1);

    expect(Banner::query()->find($b1->id))->toBeNull()
        ->and($b2->fresh()->sort_order)->toBe(1)
        ->and($b3->fresh()->sort_order)->toBe(2);
});

it('compacta orden al eliminar un banner intermedio', function (): void {
    $categoryId = BusinessCategory::query()->value('id');
    $url = 'https://firebasestorage.googleapis.com/v0/b/test/o/b.jpg?alt=media';

    $b1 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $b2 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $b3 = Banner::query()->create([
        'business_category_id' => $categoryId,
        'image_url' => $url,
        'sort_order' => 3,
        'is_active' => true,
    ]);

    $this->service->deleteAndCompact($b2);

    expect(Banner::query()->find($b2->id))->toBeNull()
        ->and($b1->fresh()->sort_order)->toBe(1)
        ->and($b3->fresh()->sort_order)->toBe(2);
});
