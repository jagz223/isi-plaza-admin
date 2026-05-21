<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\SellerInteractionEventType;
use App\Http\Controllers\Controller;
use App\Models\SellerInteractionEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sellerId = $request->user()->id;
        $since = now()->subMonth();

        $profileViews = SellerInteractionEvent::query()
            ->where('seller_user_id', $sellerId)
            ->where('event_type', SellerInteractionEventType::ProfileView)
            ->where('created_at', '>=', $since)
            ->count();

        $whatsappClicks = SellerInteractionEvent::query()
            ->where('seller_user_id', $sellerId)
            ->where('event_type', SellerInteractionEventType::WhatsappClick)
            ->where('created_at', '>=', $since)
            ->count();

        return response()->json([
            'period_label' => 'último mes',
            'profile_views_count' => $profileViews,
            'whatsapp_clicks_count' => $whatsappClicks,
        ]);
    }
}
