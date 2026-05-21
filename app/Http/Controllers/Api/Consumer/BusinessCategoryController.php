<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use Illuminate\Http\JsonResponse;

class BusinessCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BusinessCategory::query()->orderBy('sort_order')->get(['id', 'name', 'slug', 'sort_order']);

        return response()->json(['data' => $categories]);
    }
}
