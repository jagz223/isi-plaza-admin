<?php

namespace App\Providers;

use App\Contracts\MediaStorage;
use App\Services\Firebase\FakeFirebaseMediaStorage;
use App\Services\Firebase\FirebaseMediaStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MediaStorage::class, function (): MediaStorage {
            if ($this->app->environment('testing')) {
                return new FakeFirebaseMediaStorage;
            }

            return new FirebaseMediaStorage;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
