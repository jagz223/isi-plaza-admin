<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateSellerProfileRequest;
use App\Http\Resources\Admin\SellerAccountResource;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SellerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $sellers = User::query()
            ->with(['sellerProfile.businessCategory'])
            ->where('role', UserRole::Mayorista)
            ->orderByDesc('id')
            ->paginate($perPage);

        return SellerAccountResource::collection($sellers);
    }

    public function updateProfile(UpdateSellerProfileRequest $request, User $user): SellerAccountResource
    {
        abort_unless($user->role === UserRole::Mayorista, 404);

        SellerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge($request->validated(), ['user_id' => $user->id])
        );

        return SellerAccountResource::make(
            $user->fresh()->load(['sellerProfile.businessCategory'])
        );
    }

    public function destroy(User $user): JsonResponse
    {
        abort_unless($user->role === UserRole::Mayorista, 404);
        $user->delete();

        return response()->json(null, 204);
    }
}
