<?php

use App\Http\Middleware\EnsureConsumerApiUser;
use App\Http\Middleware\EnsureIsiPlazaWebSession;
use App\Http\Middleware\EnsurePlatformAccessEnabled;
use App\Http\Middleware\EnsureSellerApiUser;
use App\Http\Middleware\EnsureSellerHasActiveAccess;
use App\Http\Middleware\EnsureValidAdminToken;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OptionalSanctumAuthentication;
use App\Http\Middleware\RedirectIfIsiPlazaAdminAuthenticated;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin.token' => EnsureValidAdminToken::class,
            'isi-plaza.web' => EnsureIsiPlazaWebSession::class,
            'isi-plaza.guest' => RedirectIfIsiPlazaAdminAuthenticated::class,
            'seller.api' => EnsureSellerApiUser::class,
            'seller.active' => EnsureSellerHasActiveAccess::class,
            'consumer.api' => EnsureConsumerApiUser::class,
            'sanctum.optional' => OptionalSanctumAuthentication::class,
        ]);
        $middleware->web(prepend: [
            EnsurePlatformAccessEnabled::class,
        ]);
        $middleware->api(prepend: [
            EnsurePlatformAccessEnabled::class,
        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('seller:reset-monthly-metrics')
            ->monthlyOn(1, '00:05')
            ->timezone(config('app.timezone'))
            ->withoutOverlapping();
    })
    ->create();
