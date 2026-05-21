<?php

namespace App\Http\Middleware;

use App\Support\AdminPanelTokenValidator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidAdminToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $this->extractPlainToken($request);

        if ($plain === null) {
            return response()->json(['message' => 'Token de administración inválido o ausente.'], 401);
        }

        $adminToken = AdminPanelTokenValidator::firstMatchingActivePlain($plain, true);

        if ($adminToken === null) {
            return response()->json(['message' => 'Token de administración inválido o ausente.'], 401);
        }

        $request->attributes->set('admin_token', $adminToken);

        return $next($request);
    }

    private function extractPlainToken(Request $request): ?string
    {
        $bearer = $request->bearerToken();
        if (is_string($bearer) && $bearer !== '') {
            return $bearer;
        }

        $header = $request->header('X-Admin-Token');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        return null;
    }
}
