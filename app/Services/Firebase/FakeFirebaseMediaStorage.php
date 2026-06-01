<?php

namespace App\Services\Firebase;

use App\Contracts\MediaStorage;
use App\Support\PublicStorageUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FakeFirebaseMediaStorage implements MediaStorage
{
    private const FakeBucket = 'test.firebasestorage.app';

    public function uploadUploadedFile(UploadedFile $file, string $objectPath): string
    {
        $objectPath = ltrim($objectPath, '/');
        Storage::disk('local')->putFileAs('firebase-fake', $file, $objectPath);

        return $this->downloadUrl(self::FakeBucket, $objectPath, 'test-token');
    }

    public function deleteByStoredValue(?string $storedValue): void
    {
        if ($storedValue === null || $storedValue === '') {
            return;
        }

        $objectPath = $this->objectPathFromFakeUrl($storedValue);

        if ($objectPath !== null) {
            Storage::disk('local')->delete('firebase-fake/'.$objectPath);

            return;
        }

        if (! str_starts_with($storedValue, 'http://') && ! str_starts_with($storedValue, 'https://')) {
            Storage::disk('public')->delete($storedValue);
        }
    }

    public function resolveUrl(?string $storedValue): ?string
    {
        if ($storedValue === null || $storedValue === '') {
            return null;
        }

        if (str_starts_with($storedValue, 'http://') || str_starts_with($storedValue, 'https://')) {
            return $storedValue;
        }

        return PublicStorageUrl::forPath($storedValue);
    }

    private function downloadUrl(string $bucketName, string $objectPath, string $token): string
    {
        $encodedPath = rawurlencode($objectPath);

        return "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media&token={$token}";
    }

    private function objectPathFromFakeUrl(string $url): ?string
    {
        if (! str_contains($url, 'firebasestorage.googleapis.com')) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['path'])) {
            return null;
        }

        if (preg_match('#/o/([^?]+)#', $parts['path'], $matches) !== 1) {
            return null;
        }

        return rawurldecode($matches[1]);
    }
}
