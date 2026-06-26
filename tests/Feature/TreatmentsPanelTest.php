<?php

use App\Models\AdminToken;
use App\Models\TreatmentSection;
use Illuminate\Support\Facades\Hash;

function loginTreatmentsPanel(): void
{
    AdminToken::query()->create([
        'token_hash' => Hash::make('paneltoken12'),
        'description' => 'Test',
        'is_active' => true,
    ]);

    test()->post(route('isi-plaza.access.store'), [
        'token' => 'paneltoken12',
    ])->assertRedirect(route('isi-plaza.gestion'));
}

it('redirects guests from tratamientos panel', function (): void {
    $this->get(route('isi-plaza.tratamientos.index'))
        ->assertRedirect(route('isi-plaza.access'));
});

it('renders tratamientos panel for authenticated admin', function (): void {
    loginTreatmentsPanel();

    $this->get(route('isi-plaza.tratamientos.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('isi-plaza/tratamientos'));
});

it('creates a treatment section from the panel', function (): void {
    loginTreatmentsPanel();

    $this->post(route('isi-plaza.tratamientos.sections.store'), [
        'name' => 'Periodoncia',
    ])->assertRedirect();

    expect(TreatmentSection::query()->where('slug', 'periodoncia')->exists())->toBeTrue();
});
