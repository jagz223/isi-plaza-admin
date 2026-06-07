<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\UpdateSellerPasswordRequest;
use App\Support\SellerAppSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->sellerProfile;
        $expiresAt = $profile?->subscription_expires_at;

        return response()->json([
            'subscription_expires_at' => $expiresAt?->toIso8601String(),
            'subscription_expires_at_formatted' => $expiresAt
                ? $expiresAt->locale('es')->translatedFormat('j \d\e F \d\e Y')
                : null,
            'promotion_whatsapp_url' => SellerAppSettings::get(SellerAppSettings::PROMOTION_WHATSAPP_URL),
            'promotion_button_label' => SellerAppSettings::get(SellerAppSettings::PROMOTION_BUTTON_LABEL),
            'has_paid_promotion' => (bool) ($profile?->has_paid_promotion),
        ]);
    }

    public function updatePassword(UpdateSellerPasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}
