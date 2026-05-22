<?php

namespace App\Services\Platform;

use App\Services\Firestore\FirestoreRestClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlatformAccessService
{
    public function __construct(
        private readonly FirestoreRestClient $firestore,
    ) {}

    public function isAppEnabled(): bool
    {
        $config = config('platform.firestore');
        $cacheSeconds = (int) config('platform.cache_seconds', 5);
        $cacheKey = 'platform.app_enabled';

        $resolver = function () use ($config): bool {
            return $this->resolveEnabled(
                $config['collection'],
                $config['document'],
                $config['field'],
            );
        };

        if ($cacheSeconds > 0) {
            return Cache::remember($cacheKey, $cacheSeconds, $resolver);
        }

        return $resolver();
    }

    public function clearCache(): void
    {
        Cache::forget('platform.app_enabled');
    }

    private function resolveEnabled(string $collection, string $document, string $field): bool
    {
        try {
            $value = $this->firestore->getBooleanField($collection, $document, $field);

            if ($value === null) {
                return (bool) config('platform.fail_open', true);
            }

            return $value;
        } catch (Throwable $e) {
            Log::error('No se pudo leer app_enabled desde Firestore', [
                'message' => $e->getMessage(),
            ]);

            return (bool) config('platform.fail_open', true);
        }
    }
}
