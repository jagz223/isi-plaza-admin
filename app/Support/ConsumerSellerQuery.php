<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ConsumerSellerQuery
{
    /**
     * @return Builder<User>
     */
    public static function visibleSellers(): Builder
    {
        return User::query()
            ->where('role', UserRole::Mayorista)
            ->whereHas('sellerProfile', fn ($query) => $query->visibleToConsumers());
    }

    /**
     * @return array<int, int>
     */
    public static function favoriteMayoristaIds(Request $request): array
    {
        $user = $request->user();

        if ($user === null || $user->role !== UserRole::Comprador) {
            return [];
        }

        return $user->favoritesAsComprador()->pluck('mayorista_id')->all();
    }
}
