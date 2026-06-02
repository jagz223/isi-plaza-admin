<?php

namespace App\Http\Controllers\Api\Seller;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\StoreCatalogImageRequest;
use App\Http\Resources\Seller\CatalogImageResource;
use App\Models\CatalogImage;
use App\Services\Seller\SellerCatalogModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CatalogImageController extends Controller
{
    public function __construct(
        private MediaStorage $mediaStorage,
        private SellerCatalogModeService $catalogMode,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $profile = $request->user()->sellerProfile;

        $images = $profile
            ? $profile->catalogImages()->orderBy('display_order')->get()
            : collect();

        return CatalogImageResource::collection($images);
    }

    public function store(StoreCatalogImageRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        Log::info('seller.catalog-images.store: request', [
            'user_id' => $user->id,
            'has_image_file' => $request->hasFile('image'),
            'display_order' => $request->integer('display_order', 1),
        ]);

        if ($profile === null) {
            Log::warning('seller.catalog-images.store: missing seller_profile', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'No existe perfil de mayorista para este usuario.',
            ], 422);
        }

        if ($profile->pdf_url || $profile->excel_url) {
            return response()->json([
                'message' => 'Elimina el PDF o el Excel antes de subir imágenes al carrusel.',
            ], 422);
        }

        $maxTotal = (int) config('isi-plaza.seller.catalog_max_images_total', 25);
        $maxPerCarousel = (int) config('isi-plaza.seller.catalog_max_images_per_carousel', 5);
        $carouselCount = (int) config('isi-plaza.seller.catalog_carousel_count', 5);

        if ($profile->catalogImages()->count() >= $maxTotal) {
            return response()->json([
                'message' => "Solo puedes subir hasta {$maxTotal} imágenes de catálogo en total.",
            ], 422);
        }

        $displayOrder = $request->integer('display_order', 1);
        $displayOrder = min(max($displayOrder, 1), $carouselCount);

        $imagesInCurrentCarousel = $profile->catalogImages()->where('display_order', $displayOrder)->count();
        if ($imagesInCurrentCarousel >= $maxPerCarousel) {
            return response()->json([
                'message' => "Solo puedes subir hasta {$maxPerCarousel} imágenes por carrusel.",
            ], 422);
        }

        $extension = $request->file('image')->guessExtension() ?: 'jpg';
        $objectPath = sprintf('sellers/%d/catalog/%s.%s', $user->id, Str::uuid(), $extension);

        try {
            $imageUrl = $this->mediaStorage->uploadUploadedFile($request->file('image'), $objectPath);

            $image = CatalogImage::query()->create([
                'seller_profile_id' => $profile->id,
                'image_url' => $imageUrl,
                'display_order' => $displayOrder,
            ]);
        } catch (Throwable $exception) {
            Log::error('seller.catalog-images.store: failed', [
                'user_id' => $user->id,
                'display_order' => $displayOrder,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No se pudo guardar la imagen en el almacenamiento. Intenta de nuevo o contacta soporte.',
            ], 503);
        }

        Log::info('seller.catalog-images.store: saved', [
            'user_id' => $user->id,
            'catalog_image_id' => $image->id,
            'image_url' => $imageUrl,
            'display_order' => $displayOrder,
        ]);

        return CatalogImageResource::make($image)->response()->setStatusCode(201);
    }

    public function file(Request $request, CatalogImage $catalogImage): StreamedResponse
    {
        $profile = $request->user()->sellerProfile;

        if ($catalogImage->seller_profile_id !== $profile?->id) {
            abort(404);
        }

        try {
            $stream = $this->mediaStorage->readStream($catalogImage->image_url);
            $contentType = $this->mediaStorage->contentTypeForStoredValue($catalogImage->image_url);
        } catch (Throwable $exception) {
            Log::warning('seller.catalog-images.file: failed', [
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
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function destroy(Request $request, CatalogImage $catalogImage): JsonResponse
    {
        $profile = $request->user()->sellerProfile;

        if ($catalogImage->seller_profile_id !== $profile?->id) {
            abort(404);
        }

        $this->mediaStorage->deleteByStoredValue($catalogImage->image_url);
        $catalogImage->delete();

        return response()->json(null, 204);
    }
}
