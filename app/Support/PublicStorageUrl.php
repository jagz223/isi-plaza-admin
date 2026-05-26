<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorageUrl
{
    public static function forPath(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        $overrideBase = config('isi-plaza.public_storage_base_url');

        if (is_string($overrideBase) && $overrideBase !== '') {
            return rtrim($overrideBase, '/').'/'.ltrim($relativePath, '/');
        }

        return Storage::disk('public')->url($relativePath);
    }
}
