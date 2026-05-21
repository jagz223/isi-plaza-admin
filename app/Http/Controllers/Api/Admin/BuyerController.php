<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BuyerResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuyerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $buyers = User::query()
            ->where('role', UserRole::Comprador)
            ->orderByDesc('id')
            ->paginate($perPage);

        return BuyerResource::collection($buyers);
    }

    public function destroy(User $user): JsonResponse
    {
        abort_unless($user->role === UserRole::Comprador, 404);
        $user->delete();

        return response()->json(null, 204);
    }
}
