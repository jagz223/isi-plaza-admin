<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Enums\AccessStatus;
use App\Enums\SellerInteractionEventType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Consumer\StoreSellerInteractionRequest;
use App\Models\SellerInteractionEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InteractionController extends Controller
{
    public function store(StoreSellerInteractionRequest $request, User $seller): JsonResponse
    {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        if ($seller->sellerProfile === null || $seller->sellerProfile->access_status !== AccessStatus::Active) {
            return response()->json([
                'message' => 'No se pueden registrar eventos para este mayorista.',
            ], 422);
        }

        $actor = $request->user();
        $actorId = $actor !== null && $actor->role === UserRole::Comprador ? $actor->id : null;

        SellerInteractionEvent::query()->create([
            'seller_user_id' => $seller->id,
            'event_type' => SellerInteractionEventType::from($request->validated('event_type')),
            'actor_user_id' => $actorId,
        ]);

        return response()->json([
            'message' => 'Evento registrado.',
        ], 201);
    }
}
