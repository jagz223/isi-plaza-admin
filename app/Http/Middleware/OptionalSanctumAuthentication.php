<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuthentication
{
    /**
     * Autentica al usuario si envía Bearer token válido; no falla si falta token.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null && $request->bearerToken() !== null) {
            $token = PersonalAccessToken::findToken($request->bearerToken());

            if ($token !== null) {
                $request->setUserResolver(fn () => $token->tokenable);
            }
        }

        return $next($request);
    }
}
