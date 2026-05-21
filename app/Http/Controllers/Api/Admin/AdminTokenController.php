<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreAdminTokenRequest;
use App\Http\Resources\Admin\AdminPanelTokenResource;
use App\Models\AdminToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTokenController extends Controller
{
    public function index(): JsonResponse
    {
        $tokens = AdminToken::query()->orderByDesc('id')->get();

        return AdminPanelTokenResource::collection($tokens)->response();
    }

    public function store(StoreAdminTokenRequest $request): JsonResponse
    {
        $length = random_int(9, 15);
        $plain = Str::random($length);
        $token = AdminToken::query()->create([
            'token_hash' => Hash::make($plain),
            'description' => $request->input('description'),
            'is_active' => true,
        ]);

        return response()->json([
            'data' => AdminPanelTokenResource::make($token),
            'plain_token' => $plain,
            'message' => 'Guarda este token de forma segura; no se volverá a mostrar.',
        ], 201);
    }

    public function destroy(AdminToken $adminToken): JsonResponse
    {
        $adminToken->delete();

        return response()->json(null, 204);
    }
}
