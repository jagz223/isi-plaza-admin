<?php

namespace App\Services\Firebase;

use App\Contracts\MediaStorage;
use App\Support\PublicStorageUrl;
use Google\Cloud\Storage\Bucket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use RuntimeException;

class FirebaseMediaStorage implements MediaStorage
{
    public function uploadUploadedFile(UploadedFile $file, string $objectPath): string
    {
        $bucket = $this->bucket();
        $token = Str::uuid()->toString();
        $objectPath = ltrim($objectPath, '/');

        $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            [
                'name' => $objectPath,
                'metadata' => [
                    'contentType' => $file->getMimeType() ?: 'application/octet-stream',
                    'firebaseStorageDownloadTokens' => $token,
                ],
            ]
        );

        return $this->downloadUrl($this->bucketName(), $objectPath, $token);
    }

    public function deleteByStoredValue(?string $storedValue): void
    {
        if ($storedValue === null || $storedValue === '') {
            return;
        }

        $objectPath = $this->objectPathFromFirebaseUrl($storedValue);

        if ($objectPath !== null) {
            $this->bucket()->object($objectPath)->delete();

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

    public function downloadUrl(string $bucketName, string $objectPath, string $token): string
    {
        $encodedPath = rawurlencode($objectPath);

        return "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/{$encodedPath}?alt=media&token={$token}";
    }

    /**
     * @return Bucket
     */
    private function bucket()
    {
        $bucketName = $this->bucketName();

        if ($bucketName === '') {
            throw new RuntimeException('FIREBASE_STORAGE_DEFAULT_BUCKET no está configurado.');
        }

        return Firebase::storage()->getBucket($bucketName);
    }

    private function bucketName(): string
    {
        return (string) config('firebase.projects.app.storage.default_bucket');
    }

    private function objectPathFromFirebaseUrl(string $url): ?string
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
