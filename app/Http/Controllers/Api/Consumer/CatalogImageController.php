<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Contracts\MediaStorage;
use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CatalogImage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CatalogImageController extends Controller
{
    public function __construct(private MediaStorage $mediaStorage) {}

    public function file(User $seller, CatalogImage $catalogImage): StreamedResponse
    {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        $profile = $seller->sellerProfile;

        if ($profile === null || $profile->access_status !== AccessStatus::Active) {
            abort(404);
        }

        if ($catalogImage->seller_profile_id !== $profile->id) {
            abort(404);
        }

        try {
            $stream = $this->mediaStorage->readStream($catalogImage->image_url);
            $contentType = $this->mediaStorage->contentTypeForStoredValue($catalogImage->image_url);
        } catch (Throwable $exception) {
            Log::warning('consumer.catalog-images.file: failed', [
                'seller_id' => $seller->id,
                'catalog_image_id' => $catalogImage->id,
                'message' => $exception->getMessage(),
            ]);
            abort(404);
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
