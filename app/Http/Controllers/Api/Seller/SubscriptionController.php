<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\AccessStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->sellerProfile;
        $accessStatus = $profile?->access_status ?? AccessStatus::Pending;

        return response()->json([
            'subscription_price_label' => config('isi-plaza.seller.subscription_price_label'),
            'whatsapp_payment_url' => config('isi-plaza.seller.subscription_whatsapp_url'),
            'access_status' => $accessStatus instanceof \BackedEnum ? $accessStatus->value : $accessStatus,
            'can_access_app' => $accessStatus === AccessStatus::Active,
            'is_blocked_on_subscription_screen' => $accessStatus !== AccessStatus::Active,
            'message' => $accessStatus === AccessStatus::Active
                ? 'Tu suscripción está activa. Puedes usar el resto de la aplicación.'
                : 'Contacta por WhatsApp para completar el pago. El administrador activará tu cuenta desde el panel.',
        ]);
    }
}
