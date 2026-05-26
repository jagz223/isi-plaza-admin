<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImageBase64Storage
{
    /**
     * @return array{0: string, 1: string} [relative path on public disk, mime type]
     */
    public static function store(string $base64, string $mimeType, string $directory): array
    {
        $binary = self::decode($base64);
        $maxBytes = (int) config('isi-plaza.seller.max_image_bytes', 5 * 1024 * 1024);

        if (strlen($binary) > $maxBytes) {
            throw new InvalidArgumentException('La imagen supera el tamaño máximo permitido.');
        }

        $normalizedMime = self::normalizeMimeType($mimeType);
        $extension = self::extensionForMime($normalizedMime);
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$extension;

        Storage::disk('public')->put($path, $binary);

        return [$path, $normalizedMime];
    }

    public static function decode(string $base64): string
    {
        $payload = $base64;

        if (str_contains($payload, ',')) {
            $payload = (string) str($payload)->after(',');
        }

        $payload = preg_replace('/\s+/', '', $payload) ?? $payload;
        $decoded = base64_decode($payload, true);

        if ($decoded === false || $decoded === '') {
            throw new InvalidArgumentException('image_base64 no es válido.');
        }

        return $decoded;
    }

    public static function normalizeMimeType(string $mimeType): string
    {
        $mime = strtolower(trim($mimeType));

        return match ($mime) {
            'image/jpg', 'image/jpeg' => 'image/jpeg',
            'image/png' => 'image/png',
            'image/webp' => 'image/webp',
            default => throw new InvalidArgumentException('mime_type no soportado. Use image/jpeg, image/png o image/webp.'),
        };
    }

    public static function extensionForMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
    }
}
