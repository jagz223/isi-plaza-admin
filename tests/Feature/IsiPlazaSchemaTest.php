<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('crea las tablas del esquema isi plaza', function () {
    expect(Schema::hasTable('business_categories'))->toBeTrue();
    expect(Schema::hasTable('seller_profiles'))->toBeTrue();
    expect(Schema::hasTable('catalog_images'))->toBeTrue();
    expect(Schema::hasTable('favorites'))->toBeTrue();
    expect(Schema::hasTable('banners'))->toBeTrue();
    expect(Schema::hasTable('admin_tokens'))->toBeTrue();
    expect(Schema::hasTable('seller_interaction_events'))->toBeTrue();
});

it('siembra las diez categorías de negocio del proyecto', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\BusinessCategorySeeder']);

    expect(DB::table('business_categories')->count())->toBe(10);
    expect(DB::table('business_categories')->where('slug', 'tecnologia-electronica')->exists())->toBeTrue();
});

it('añade columnas isi plaza a users', function () {
    expect(Schema::hasColumns('users', ['role', 'provider', 'provider_id']))->toBeTrue();
});
