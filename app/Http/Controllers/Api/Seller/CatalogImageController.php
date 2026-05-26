<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\StoreCatalogImageRequest;
use App\Http\Resources\Seller\CatalogImageResource;
use App\Models\CatalogImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CatalogImageController extends Controller
{
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
            'content_type' => $request->header('Content-Type'),
            'payload_keys' => array_keys($request->except(['image'])),
            'validated' => $request->validated(),
            'has_image_file' => $request->hasFile('image'),
            'all_file_keys' => array_keys($request->allFiles()),
            'image_meta' => $request->hasFile('image') ? [
                'original_name' => $request->file('image')->getClientOriginalName(),
                'size' => $request->file('image')->getSize(),
                'mime_type' => $request->file('image')->getMimeType(),
                'is_valid' => $request->file('image')->isValid(),
                'error' => $request->file('image')->getError(),
            ] : null,
        ]);

        if ($profile === null) {
            Log::warning('seller.catalog-images.store: missing seller_profile', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'No existe perfil de mayorista para este usuario.',
            ], 422);
        }

        $maxTotal = 25;
        $maxPerCarousel = 5;

        if ($profile->catalogImages()->count() >= $maxTotal) {
            return response()->json([
                'message' => "Solo puedes subir hasta {$maxTotal} imágenes de catálogo en total.",
            ], 422);
        }

        $displayOrder = $request->integer('display_order', 1);
        $displayOrder = min(max($displayOrder, 1), 5); // 5 carousels max

        $imagesInCurrentCarousel = $profile->catalogImages()->where('display_order', $displayOrder)->count();
        if ($imagesInCurrentCarousel >= $maxPerCarousel) {
            return response()->json([
                'message' => "Solo puedes subir hasta {$maxPerCarousel} imágenes por carrusel.",
            ], 422);
        }

        $path = $request->file('image')->store('catalog', 'public');

        $image = CatalogImage::query()->create([
            'seller_profile_id' => $profile->id,
            'image_path' => $path,
            'display_order' => $displayOrder,
        ]);

        Log::info('seller.catalog-images.store: saved', [
            'user_id' => $user->id,
            'catalog_image_id' => $image->id,
            'seller_profile_id' => $profile->id,
            'image_path' => $path,
            'display_order' => $displayOrder,
            'image_url' => Storage::disk('public')->url($path),
        ]);

        return CatalogImageResource::make($image)->response()->setStatusCode(201);
    }

    public function destroy(Request $request, CatalogImage $catalogImage): JsonResponse
    {
        $profile = $request->user()->sellerProfile;

        if ($catalogImage->seller_profile_id !== $profile?->id) {
            abort(404);
        }

        Storage::disk('public')->delete($catalogImage->image_path);
        $catalogImage->delete();

        return response()->json(null, 204);
    }
}
