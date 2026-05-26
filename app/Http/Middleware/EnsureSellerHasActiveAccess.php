<?php

namespace App\Http\Middleware;

use App\Enums\AccessStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerHasActiveAccess
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = $user?->sellerProfile;

        if ($profile === null || $profile->access_status !== AccessStatus::Active || ($profile->subscription_expires_at && $profile->subscription_expires_at->isPast())) {
            return response()->json([
                'message' => 'Tu cuenta aún no tiene acceso activo o tu suscripción ha expirado. Completa la suscripción y espera la autorización del administrador.',
                'access_status' => $profile?->access_status?->value ?? AccessStatus::Pending->value,
            ], 403);
        }

        return $next($request);
    }
}
