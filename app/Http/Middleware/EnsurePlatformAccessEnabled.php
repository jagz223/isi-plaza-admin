<?php

namespace App\Http\Middleware;

use App\Services\Platform\PlatformAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAccessEnabled
{
    public function __construct(
        private readonly PlatformAccessService $platformAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up')) {
            return $next($request);
        }

        if ($this->platformAccess->isAppEnabled()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Plataforma no disponible.',
                'platform_disabled' => true,
            ], 503);
        }

        return response()
            ->view('platform-disabled')
            ->setStatusCode(503);
    }
}
