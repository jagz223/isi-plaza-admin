<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Support\ConsumerSellerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function countries(): JsonResponse
    {
        $configured = config('isi-plaza.consumer.filter_countries', []);
        $available = ConsumerSellerQuery::visibleSellers()
            ->join('seller_profiles', 'users.id', '=', 'seller_profiles.user_id')
            ->whereNotNull('seller_profiles.country')
            ->distinct()
            ->orderBy('seller_profiles.country')
            ->pluck('seller_profiles.country')
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'data' => collect($configured)->map(fn (string $country) => [
                'name' => $country,
                'has_sellers' => in_array($country, $available, true),
            ])->values(),
        ]);
    }

    public function states(Request $request): JsonResponse
    {
        $request->validate([
            'country' => ['required', 'string', 'max:100'],
        ]);

        $states = ConsumerSellerQuery::visibleSellers()
            ->join('seller_profiles', 'users.id', '=', 'seller_profiles.user_id')
            ->where('seller_profiles.country', $request->string('country'))
            ->whereNotNull('seller_profiles.state')
            ->distinct()
            ->orderBy('seller_profiles.state')
            ->pluck('seller_profiles.state')
            ->filter()
            ->values();

        return response()->json([
            'country' => $request->string('country'),
            'data' => $states,
        ]);
    }
}
