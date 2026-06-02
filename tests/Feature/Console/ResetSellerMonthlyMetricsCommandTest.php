<?php

use App\Enums\SellerInteractionEventType;
use App\Models\SellerInteractionEvent;
use App\Models\User;

it('elimina eventos de interacción de meses anteriores', function (): void {
    $seller = User::factory()->mayorista()->create();

    SellerInteractionEvent::query()->create([
        'seller_user_id' => $seller->id,
        'event_type' => SellerInteractionEventType::WhatsappClick,
        'created_at' => now()->startOfMonth()->subDay(),
    ]);
    SellerInteractionEvent::query()->create([
        'seller_user_id' => $seller->id,
        'event_type' => SellerInteractionEventType::WebsiteClick,
        'created_at' => now()->startOfMonth()->addDay(),
    ]);

    $this->artisan('seller:reset-monthly-metrics')->assertSuccessful();

    expect(SellerInteractionEvent::query()->count())->toBe(1);
});
