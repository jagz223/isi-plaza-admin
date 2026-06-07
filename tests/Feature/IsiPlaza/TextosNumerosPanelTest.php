<?php

use App\Models\AdminToken;
use App\Models\AppSetting;
use App\Support\SellerAppSettings;
use Illuminate\Support\Facades\Hash;

function loginIsiPlazaPanel(): void
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

it('redirects guests from textos y números to access', function (): void {
    $this->get(route('isi-plaza.textos-numeros.index'))
        ->assertRedirect(route('isi-plaza.access'));
});

it('renders textos y números for authenticated admin', function (): void {
    loginIsiPlazaPanel();

    $content = $this->get(route('isi-plaza.textos-numeros.index'))
        ->assertOk()
        ->getContent();

    expect($content)->toContain('isi-plaza\/textos-numeros');
});

it('updates seller app texts and whatsapp urls from the panel', function (): void {
    loginIsiPlazaPanel();

    $this->patch(route('isi-plaza.textos-numeros.update'), [
        'subscription_plan_label' => 'Plan premium',
        'subscription_price_label' => 'Suscripción mensual de 99 MXN',
        'subscription_message_pending' => 'Escríbenos por WhatsApp para activar tu cuenta.',
        'subscription_message_active' => 'Tu plan está activo.',
        'subscription_whatsapp_url' => 'https://wa.me/5215511111111?text=Hola',
        'promotion_whatsapp_url' => 'https://wa.me/5215522222222?text=Promo',
        'subscribe_button_label' => 'Quiero suscribirme',
        'promotion_button_label' => 'Quiero promoción',
    ])->assertRedirect(route('isi-plaza.textos-numeros.index'));

    expect(SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_PLAN_LABEL))->toBe('Plan premium')
        ->and(SellerAppSettings::get(SellerAppSettings::PROMOTION_BUTTON_LABEL))->toBe('Quiero promoción')
        ->and(AppSetting::query()->count())->toBeGreaterThanOrEqual(8);
});
