<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Consumer\ConsumerSellerListResource;
use App\Models\Favorite;
use App\Models\User;
use App\Support\ConsumerSellerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favoriteIds = $request->user()
            ->favoritesAsComprador()
            ->pluck('mayorista_id')
            ->all();

        $sellers = ConsumerSellerQuery::visibleSellers()
            ->whereIn('id', $favoriteIds)
            ->with(['sellerProfile.businessCategory'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $sellers->map(
                fn (User $user) => (new ConsumerSellerListResource($user, $favoriteIds))->resolve($request)
            )->values(),
        ]);
    }

    public function store(User $seller): JsonResponse
    {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        if ($seller->sellerProfile === null || $seller->sellerProfile->access_status !== AccessStatus::Active) {
            return response()->json([
                'message' => 'No puedes guardar un mayorista que no está activo.',
            ], 422);
        }

        /** @var User $comprador */
        $comprador = request()->user();

        Favorite::query()->firstOrCreate([
            'comprador_id' => $comprador->id,
            'mayorista_id' => $seller->id,
        ]);

        return response()->json([
            'message' => 'Mayorista guardado en favoritos.',
            'is_favorited' => true,
        ], 201);
    }

    public function destroy(User $seller): JsonResponse
    {
        Favorite::query()
            ->where('comprador_id', request()->user()->id)
            ->where('mayorista_id', $seller->id)
            ->delete();

        return response()->json([
            'message' => 'Mayorista eliminado de favoritos.',
            'is_favorited' => false,
        ]);
    }
}
