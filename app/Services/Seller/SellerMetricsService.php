<?php

namespace App\Services\Seller;

use App\Enums\SellerInteractionEventType;
use App\Models\SellerInteractionEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class SellerMetricsService
{
    public function periodStart(?CarbonInterface $at = null): CarbonInterface
    {
        return ($at ?? now())->copy()->startOfMonth();
    }

    public function periodLabel(?CarbonInterface $at = null): string
    {
        $month = ($at ?? now())->locale('es')->translatedFormat('F Y');

        return mb_convert_case($month, MB_CASE_TITLE, 'UTF-8');
    }

    public function countForSeller(int $sellerUserId, SellerInteractionEventType $type, ?CarbonInterface $at = null): int
    {
        return SellerInteractionEvent::query()
            ->where('seller_user_id', $sellerUserId)
            ->where('event_type', $type)
            ->where('created_at', '>=', $this->periodStart($at))
            ->count();
    }

    /**
     * @return array{period_label: string, whatsapp_clicks_count: int, website_clicks_count: int}
     */
    public function metricsForSeller(int $sellerUserId, ?CarbonInterface $at = null): array
    {
        return [
            'period_label' => $this->periodLabel($at),
            'whatsapp_clicks_count' => $this->countForSeller($sellerUserId, SellerInteractionEventType::WhatsappClick, $at),
            'website_clicks_count' => $this->countForSeller($sellerUserId, SellerInteractionEventType::WebsiteClick, $at),
        ];
    }

    public function purgeEventsBeforeCurrentMonth(?CarbonInterface $at = null): int
    {
        $cutoff = $this->periodStart($at);

        return DB::table('seller_interaction_events')
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}
