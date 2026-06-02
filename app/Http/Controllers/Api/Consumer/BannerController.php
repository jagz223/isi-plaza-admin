<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Consumer\ConsumerBannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->integer('business_category_id');

        $banners = Banner::query()
            ->where('is_active', true)
            ->when($categoryId > 0, fn ($query) => $query->where('business_category_id', $categoryId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => ConsumerBannerResource::collection($banners),
        ]);
    }

    public function recordClick(Banner $banner): JsonResponse
    {
        $banner->increment('clicks_count');

        return response()->json([
            'message' => 'Clic registrado.',
            'clicks_count' => $banner->fresh()->clicks_count,
        ]);
    }
}
