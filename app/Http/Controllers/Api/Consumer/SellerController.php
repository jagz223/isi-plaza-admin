<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consumer\ListSellersRequest;
use App\Http\Resources\Consumer\ConsumerSellerDetailResource;
use App\Http\Resources\Consumer\ConsumerSellerListResource;
use App\Models\Favorite;
use App\Models\User;
use App\Support\ConsumerSellerQuery;
use Illuminate\Http\JsonResponse;

class SellerController extends Controller
{
    public function index(ListSellersRequest $request): JsonResponse
    {
        $favoriteIds = ConsumerSellerQuery::favoriteMayoristaIds($request);

        $query = ConsumerSellerQuery::visibleSellers()
            ->with(['sellerProfile.businessCategory'])
            ->orderBy('name');

        if ($request->filled('business_category_id')) {
            $query->whereHas('sellerProfile', fn ($q) => $q
                ->where('business_category_id', $request->integer('business_category_id')));
        }

        if ($request->filled('country')) {
            $query->whereHas('sellerProfile', fn ($q) => $q
                ->where('country', $request->string('country')));
        }

        if ($request->filled('state')) {
            $query->whereHas('sellerProfile', function ($q) use ($request) {
                // Compatible with both string and JSON array
                $q->where(function($sq) use ($request) {
                    $sq->where('state', 'like', '%"'.$request->string('state').'"%')
                      ->orWhere('state', $request->string('state'));
                });
            });
        }

        $perPage = $request->integer('per_page', config('isi-plaza.consumer.sellers_per_page', 20));

        $sellers = $query->paginate($perPage);

        return response()->json([
            'data' => $sellers->getCollection()->map(
                fn (User $user) => (new ConsumerSellerListResource($user, $favoriteIds))->resolve($request)
            )->values(),
            'meta' => [
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
                'from' => $sellers->firstItem(),
                'to' => $sellers->lastItem(),
            ],
            'links' => [
                'first' => $sellers->url(1),
                'last' => $sellers->url($sellers->lastPage()),
                'prev' => $sellers->previousPageUrl(),
                'next' => $sellers->nextPageUrl(),
            ],
        ]);
    }

    public function show(User $seller, ListSellersRequest $request): ConsumerSellerDetailResource|JsonResponse
    {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        $seller->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages']);

        if ($seller->sellerProfile === null || $seller->sellerProfile->access_status !== AccessStatus::Active) {
            return response()->json([
                'message' => 'Este mayorista no está disponible.',
            ], 404);
        }

        $isFavorited = false;
        $comprador = $request->user();

        if ($comprador !== null && $comprador->role === UserRole::Comprador) {
            $isFavorited = Favorite::query()
                ->where('comprador_id', $comprador->id)
                ->where('mayorista_id', $seller->id)
                ->exists();
        }

        return new ConsumerSellerDetailResource($seller, $isFavorited);
    }
}
