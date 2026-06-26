<?php

namespace App\Providers;

use App\Contracts\MediaStorage;
use App\Database\Connectors\RenderPostgresConnector;
use App\Services\Firebase\FakeFirebaseMediaStorage;
use App\Services\Firebase\FirebaseMediaStorage;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', function (): PostgresConnector {
            if (filter_var(env('DB_PG_LEGACY_ENDPOINT', false), FILTER_VALIDATE_BOOL)) {
                return new RenderPostgresConnector;
            }

            return new PostgresConnector;
        });

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
