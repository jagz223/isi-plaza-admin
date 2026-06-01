<?php

namespace App\Support;

use App\Contracts\MediaStorage;

class MediaUrl
{
    public static function resolve(?string $storedValue): ?string
    {
        return app(MediaStorage::class)->resolveUrl($storedValue);
    }
}
