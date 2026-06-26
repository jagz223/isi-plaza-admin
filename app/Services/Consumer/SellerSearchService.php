<?php

namespace App\Services\Consumer;

use App\Enums\AccessStatus;
use App\Http\Requests\Api\Consumer\ListSellersRequest;
use App\Models\SellerProfile;
use App\Models\User;
use App\Support\ConsumerSellerQuery;
use App\Support\Geo\GeoDistance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerSearchService
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(ListSellersRequest $request): LengthAwarePaginator
    {
        $perPage = $request->integer('per_page', config('isi-plaza.consumer.sellers_per_page', 20));

        return $this->buildQuery($request)->paginate($perPage);
    }

    /**
     * @return Builder<User>
     */
    public function buildQuery(ListSellersRequest $request): Builder
    {
        $query = ConsumerSellerQuery::visibleSellers()
            ->with(['sellerProfile.businessCategory']);

        if ($request->filled('latitude') && $request->filled('longitude')) {
            return $this->applyGeoSearch($query, $request);
        }

        $query->whereHas('sellerProfile', fn (Builder $profile) => $this->applyProfileFilters($profile, $request));

        return $query->orderBy('name');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    protected function applyGeoSearch(Builder $query, ListSellersRequest $request): Builder
    {
        $latitude = $request->float('latitude');
        $longitude = $request->float('longitude');
        $radius = $request->float(
            'radius_km',
            (float) config('odontica-geo.default_radius_km', 20),
        );
        $expression = GeoDistance::haversineExpression('seller_profiles.latitude', 'seller_profiles.longitude');
        $bindings = GeoDistance::bindings($latitude, $longitude);

        $query->join('seller_profiles', 'users.id', '=', 'seller_profiles.user_id')
            ->where('seller_profiles.access_status', AccessStatus::Active)
            ->whereNotNull('seller_profiles.latitude')
            ->whereNotNull('seller_profiles.longitude');

        $this->applyProfileFilters($query, $request);

        if ($request->filled('treatment_id')) {
            $query->whereExists(function ($subquery) use ($request) {
                $subquery->selectRaw('1')
                    ->from('doctor_services')
                    ->whereColumn('doctor_services.seller_profile_id', 'seller_profiles.id')
                    ->where('doctor_services.treatment_id', $request->integer('treatment_id'));
            });
        }

        return $query
            ->select('users.*')
            ->selectRaw("({$expression}) as distance_km", $bindings)
            ->whereRaw("({$expression}) <= ?", [...$bindings, $radius])
            ->orderBy('distance_km');
    }

    /**
     * @param  Builder<SellerProfile>|Builder<User>  $profile
     */
    protected function applyProfileFilters(Builder $profile, ListSellersRequest $request): void
    {
        if ($request->filled('business_category_id')) {
            $profile->where('business_category_id', $request->integer('business_category_id'));
        }

        if ($request->filled('treatment_id') && ! $this->isJoinedUserQuery($profile)) {
            $profile->whereHas('doctorServices', fn (Builder $services) => $services
                ->where('treatment_id', $request->integer('treatment_id')));
        }

        if ($request->filled('country')) {
            $profile->where('country', $request->string('country'));
        }

        if ($request->filled('state')) {
            $this->applyStateFilter($profile, $request->string('state'));
        }

        if ($request->filled('region')) {
            $this->applyRegionFilter($profile, $request->string('region'));
        }

        if ($request->filled('municipality')) {
            $profile->where('municipality', $request->string('municipality'));
        }
    }

    /**
     * @param  Builder<SellerProfile>|Builder<User>  $profile
     */
    protected function applyStateFilter(Builder $profile, string $state): void
    {
        $profile->where(function (Builder $query) use ($state) {
            $query->where('state', 'like', '%"'.$state.'"%')
                ->orWhere('state', $state);
        });
    }

    /**
     * @param  Builder<SellerProfile>|Builder<User>  $profile
     */
    protected function applyRegionFilter(Builder $profile, string $regionKey): void
    {
        /** @var array{state_names?: array<int, string>, municipalities?: array<int, string>}|null $region */
        $region = config("odontica-geo.regions.{$regionKey}");

        if (! is_array($region)) {
            return;
        }

        $stateNames = $region['state_names'] ?? [];
        $municipalities = $region['municipalities'] ?? [];

        $profile->where(function (Builder $query) use ($stateNames, $municipalities) {
            foreach ($stateNames as $stateName) {
                $query->orWhere(function (Builder $stateQuery) use ($stateName) {
                    $this->applyStateFilter($stateQuery, $stateName);
                });
            }

            if ($municipalities !== []) {
                $query->orWhereIn('municipality', $municipalities);
            }
        });
    }

    /**
     * @param  Builder<SellerProfile>|Builder<User>  $query
     */
    protected function isJoinedUserQuery(Builder $query): bool
    {
        return $query->getModel() instanceof User;
    }
}
