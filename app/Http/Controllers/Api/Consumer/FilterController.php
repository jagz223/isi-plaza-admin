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

        $rawStates = ConsumerSellerQuery::visibleSellers()
            ->join('seller_profiles', 'users.id', '=', 'seller_profiles.user_id')
            ->where('seller_profiles.country', $request->string('country'))
            ->whereNotNull('seller_profiles.state')
            ->distinct()
            ->pluck('seller_profiles.state')
            ->filter();

        $expanded = [];
        foreach ($rawStates as $state) {
            $value = (string) $state;
            if (str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (is_string($item) && $item !== '') {
                            $expanded[] = $item;
                        }
                    }

                    continue;
                }
            }
            if ($value !== '') {
                $expanded[] = $value;
            }
        }

        $states = collect($expanded)->unique()->sort()->values();

        return response()->json([
            'country' => $request->string('country'),
            'data' => $states,
        ]);
    }

    public function regions(): JsonResponse
    {
        $regions = collect(config('odontica-geo.regions', []))
            ->map(fn (array $region, string $key) => [
                'key' => $key,
                'label' => $region['label'] ?? $key,
            ])
            ->values();

        return response()->json([
            'data' => $regions,
        ]);
    }

    public function municipalities(Request $request): JsonResponse
    {
        $request->validate([
            'region' => ['required', 'string', 'in:cdmx,edo_mex'],
        ]);

        $regionKey = $request->string('region');
        /** @var array{municipalities?: array<int, string>}|null $region */
        $region = config("odontica-geo.regions.{$regionKey}");

        $municipalities = collect($region['municipalities'] ?? [])
            ->sort()
            ->values();

        return response()->json([
            'region' => $regionKey,
            'data' => $municipalities,
        ]);
    }
}
