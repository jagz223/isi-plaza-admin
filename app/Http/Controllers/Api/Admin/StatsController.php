<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminToken;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'buyers_count' => User::query()->where('role', UserRole::Comprador)->count(),
            'sellers_count' => User::query()->where('role', UserRole::Mayorista)->count(),
            'banners_count' => Banner::query()->count(),
            'active_admin_tokens_count' => AdminToken::query()->where('is_active', true)->count(),
        ]);
    }
}
