<?php

namespace App\Services\Firebase;

use App\Contracts\MediaStorage;
use App\Support\PublicStorageUrl;
use Google\Cloud\Storage\Bucket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use RuntimeException;
use Throwable;

class FirebaseMediaStorage implements MediaStorage
{
    /** URLs firmadas para lectura en la app (las reglas de Storage suelen bloquear GET público). */
    private const SignedUrlTtlDays = 7;

    public function uploadUploadedFile(UploadedFile $file, string $objectPath): string
    {
        $bucket = $this->bucket();
        $token = Str::uuid()->toString();
        $objectPath = ltrim($objectPath, '/');

        $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            [
                'name' => $objectPath,
                'contentType' => $file->getMimeType() ?: 'application/octet-stream',
                'metadata' => [
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
            if (str_contains($storedValue, 'firebasestorage.googleapis.com')) {
                return $this->resolveFirebaseDownloadUrl($storedValue);
            }

            return $storedValue;
        }

        return PublicStorageUrl::forPath($storedValue);
    }

    /**
     * Genera URL firmada con la cuenta de servicio (evita 403 por reglas de Storage).
     * En BD se sigue guardando la URL con token de Firebase; solo la respuesta API usa firma fresca.
     */
    private function resolveFirebaseDownloadUrl(string $storedUrl): string
    {
        $objectPath = $this->objectPathFromFirebaseUrl($storedUrl);

        if ($objectPath === null) {
            return $storedUrl;
        }

        try {
            $object = $this->bucket()->object($objectPath);

            if (! $object->exists()) {
                Log::warning('firebase.resolveUrl: object not found', ['path' => $objectPath]);

                return $storedUrl;
            }

            return $object->signedUrl(
                new \DateTimeImmutable('+'.self::SignedUrlTtlDays.' days'),
                ['version' => 'v4'],
            );
        } catch (Throwable $exception) {
            Log::warning('firebase.resolveUrl: signed url failed', [
                'path' => $objectPath,
                'message' => $exception->getMessage(),
            ]);

            return $storedUrl;
        }
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

    public function readStream(string $storedValue)
    {
        $objectPath = $this->objectPathFromFirebaseUrl($storedValue);

        if ($objectPath === null) {
            throw new RuntimeException('No se pudo resolver la ruta del archivo en Storage.');
        }

        $object = $this->bucket()->object($objectPath);

        if (! $object->exists()) {
            throw new RuntimeException('El archivo no existe en Storage.');
        }

        return $object->downloadAsStream()->detach();
    }

    public function contentTypeForStoredValue(string $storedValue): string
    {
        $objectPath = $this->objectPathFromFirebaseUrl($storedValue);

        if ($objectPath === null) {
            return 'application/octet-stream';
        }

        $info = $this->bucket()->object($objectPath)->info();

        return $info['contentType'] ?? 'image/jpeg';
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
