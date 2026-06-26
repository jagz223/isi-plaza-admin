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
use App\Services\Consumer\SellerSearchService;
use App\Support\ConsumerSellerQuery;
use Illuminate\Http\JsonResponse;

class SellerController extends Controller
{
    public function __construct(
        protected SellerSearchService $sellerSearch,
    ) {}

    public function index(ListSellersRequest $request): JsonResponse
    {
        $favoriteIds = ConsumerSellerQuery::favoriteMayoristaIds($request);
        $sellers = $this->sellerSearch->paginate($request);

        $payload = [
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
        ];

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $payload['meta']['geo'] = [
                'latitude' => $request->float('latitude'),
                'longitude' => $request->float('longitude'),
                'radius_km' => $request->float(
                    'radius_km',
                    (float) config('odontica-geo.default_radius_km', 20),
                ),
                'sorted_by' => 'distance',
            ];
        }

        return response()->json($payload);
    }

    public function show(User $seller, ListSellersRequest $request): ConsumerSellerDetailResource|JsonResponse
    {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        $seller->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages', 'sellerProfile.doctorServices.treatment.section']);

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
