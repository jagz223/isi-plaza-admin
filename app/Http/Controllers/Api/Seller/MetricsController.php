<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Services\Seller\SellerMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function __construct(private SellerMetricsService $metrics) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->metrics->metricsForSeller($request->user()->id)
        );
    }
}
