<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\BusinessCategory;
use Illuminate\Http\JsonResponse;

class BusinessCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BusinessCategory::query()->orderBy('id')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $categories]);
    }
}
