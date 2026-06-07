<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\AccessStatus;
use App\Http\Controllers\Controller;
use App\Support\SellerAppSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->sellerProfile;
        $accessStatus = $profile?->access_status ?? AccessStatus::Pending;

        return response()->json([
            'subscription_plan_label' => SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_PLAN_LABEL),
            'subscription_price_label' => SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_PRICE_LABEL),
            'subscribe_button_label' => SellerAppSettings::get(SellerAppSettings::SUBSCRIBE_BUTTON_LABEL),
            'whatsapp_payment_url' => SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_WHATSAPP_URL),
            'access_status' => $accessStatus instanceof \BackedEnum ? $accessStatus->value : $accessStatus,
            'can_access_app' => $accessStatus === AccessStatus::Active,
            'is_blocked_on_subscription_screen' => $accessStatus !== AccessStatus::Active,
            'message' => $accessStatus === AccessStatus::Active
                ? SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_MESSAGE_ACTIVE)
                : SellerAppSettings::get(SellerAppSettings::SUBSCRIPTION_MESSAGE_PENDING),
        ]);
    }
}
