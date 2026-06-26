<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\TreatmentSectionResource;
use App\Models\TreatmentSection;
use Illuminate\Http\JsonResponse;

class TreatmentController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = TreatmentSection::query()
            ->where('is_active', true)
            ->with(['treatments' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => TreatmentSectionResource::collection($sections),
        ]);
    }
}
