<?php

use App\Enums\AccessStatus;
use App\Models\AdminToken;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates a thirty day subscription window when granting mayorista access', function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    $this->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ])->assertRedirect(route('isi-plaza.gestion'));

    $seller = User::factory()->mayorista()->create();

    $this->from(route('isi-plaza.gestion'))
        ->patch(route('isi-plaza.vendedores.update', $seller), [
            'access_status' => 'active',
        ])
        ->assertRedirect(route('isi-plaza.gestion'));

    $profile = SellerProfile::query()->where('user_id', $seller->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->access_status)->toBe(AccessStatus::Active);

    expect($profile->subscription_granted_at)->not->toBeNull()
        ->and($profile->subscription_expires_at)->not->toBeNull();

    expect($profile->subscription_expires_at->equalTo($profile->subscription_granted_at->copy()->addDays(30)))->toBeTrue();
});

it('clears subscription dates when access is denied', function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    $this->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ]);

    $seller = User::factory()->mayorista()->create();

    $this->from(route('isi-plaza.gestion'))
        ->patch(route('isi-plaza.vendedores.update', $seller), [
            'access_status' => 'active',
        ]);

    $this->from(route('isi-plaza.gestion'))
        ->patch(route('isi-plaza.vendedores.update', $seller), [
            'access_status' => 'denied',
        ])
        ->assertRedirect(route('isi-plaza.gestion'));

    $profile = SellerProfile::query()->where('user_id', $seller->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->access_status)->toBe(AccessStatus::Denied)
        ->and($profile->subscription_granted_at)->toBeNull()
        ->and($profile->subscription_expires_at)->toBeNull();
});
