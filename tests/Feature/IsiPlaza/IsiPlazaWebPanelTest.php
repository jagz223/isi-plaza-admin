<?php

use App\Models\AdminToken;
use Illuminate\Support\Facades\Hash;

it('renders the token access page', function (): void {
    $content = $this->get(route('isi-plaza.access'))->assertOk()->getContent();

    expect($content)->toContain('isi-plaza\/access');
});

it('redirects to gestión when the token is valid', function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    $this->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ])->assertRedirect(route('isi-plaza.gestion'));

    $content = $this->get(route('isi-plaza.gestion'))->assertOk()->getContent();

    expect($content)->toContain('isi-plaza\/gestion');
});

it('redirects guests from gestión to access', function (): void {
    $this->get(route('isi-plaza.gestion'))->assertRedirect(route('isi-plaza.access'));
});

it('redirects guests from panel root to access', function (): void {
    $this->get(route('isi-plaza.panel'))->assertRedirect(route('isi-plaza.access'));
});

it('redirects authenticated admin from panel root to gestión', function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    $this->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ])->assertRedirect(route('isi-plaza.gestion'));

    $this->get(route('isi-plaza.panel'))->assertRedirect(route('isi-plaza.gestion'));
});

it('shows token access when iniciar clears an existing panel session', function (): void {
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    $this->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ])->assertRedirect(route('isi-plaza.gestion'));

    $content = $this->get(route('isi-plaza.access', ['iniciar' => 1]))->assertOk()->getContent();

    expect($content)->toContain('isi-plaza\/access');
});
