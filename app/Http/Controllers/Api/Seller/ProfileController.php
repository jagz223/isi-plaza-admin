<?php

namespace App\Http\Controllers\Api\Seller;

use App\Contracts\MediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Seller\UpdateSellerProfileRequest;
use App\Http\Resources\Seller\SellerAccountResource;
use App\Models\SellerProfile;
use App\Services\Seller\SellerCatalogModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct(
        private MediaStorage $mediaStorage,
        private SellerCatalogModeService $catalogMode,
    ) {}

    public function show(Request $request): SellerAccountResource
    {
        $user = $request->user()->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages']);

        Log::info('seller.profile.show', [
            'user_id' => $user->id,
            'seller_profile_id' => $user->sellerProfile?->id,
            'access_status' => $user->sellerProfile?->access_status instanceof \BackedEnum
                ? $user->sellerProfile->access_status->value
                : $user->sellerProfile?->access_status,
            'business_category_id' => $user->sellerProfile?->business_category_id,
            'description' => $user->sellerProfile?->description,
            'country' => $user->sellerProfile?->country,
            'state' => $user->sellerProfile?->state,
        ]);

        return SellerAccountResource::make($user);
    }

    public function update(UpdateSellerProfileRequest $request): SellerAccountResource
    {
        $user = $request->user();
        $profile = SellerProfile::query()->firstOrCreate(['user_id' => $user->id]);

        Log::info('seller.profile.update: request', [
            'user_id' => $user->id,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'payload_keys' => array_keys($request->except(['avatar', 'pdf', 'excel'])),
            'validated' => $request->safe()->except(['avatar', 'pdf', 'excel']),
            'has_avatar_file' => $request->hasFile('avatar'),
            'has_pdf_file' => $request->hasFile('pdf'),
            'has_excel_file' => $request->hasFile('excel'),
            'all_file_keys' => array_keys($request->allFiles()),
            'files_meta' => self::describeUploadedFiles($request),
        ]);

        $safe = $request->safe();
        $data = $safe->except(['avatar', 'pdf', 'excel', 'name']);

        if ($safe->has('name')) {
            $user->update(['name' => $safe->input('name')]);
        }

        if ($request->hasFile('avatar')) {
            $this->mediaStorage->deleteByStoredValue($profile->avatar_url);
            $extension = $request->file('avatar')->guessExtension() ?: 'jpg';
            $data['avatar_url'] = $this->mediaStorage->uploadUploadedFile(
                $request->file('avatar'),
                "sellers/{$user->id}/avatar.{$extension}"
            );
        }

        if ($request->hasFile('pdf')) {
            $pdfUrl = $this->mediaStorage->uploadUploadedFile(
                $request->file('pdf'),
                "sellers/{$user->id}/documents/catalog.pdf"
            );
            $this->mediaStorage->deleteByStoredValue($profile->pdf_url);
            $this->catalogMode->applyPdfUpload($profile, $pdfUrl);
            unset($data['pdf_url']);
        }

        if ($request->hasFile('excel')) {
            $extension = $request->file('excel')->guessExtension() ?: 'xlsx';
            $excelUrl = $this->mediaStorage->uploadUploadedFile(
                $request->file('excel'),
                "sellers/{$user->id}/documents/catalog.{$extension}"
            );
            $this->mediaStorage->deleteByStoredValue($profile->excel_url);
            $this->catalogMode->applyExcelUpload($profile, $excelUrl);
            unset($data['excel_url']);
        }

        if ($data === []) {
            Log::warning('seller.profile.update: empty validated payload — no columns will change', [
                'user_id' => $user->id,
                'seller_profile_id' => $profile->id,
            ]);
        }

        $profile->update($data);
        $profile->refresh();

        Log::info('seller.profile.update: after', [
            'user_id' => $user->id,
            'seller_profile_id' => $profile->id,
            'avatar_url' => $profile->avatar_url,
        ]);

        return SellerAccountResource::make(
            $user->fresh()->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages'])
        );
    }

    public function destroyPdf(Request $request): SellerAccountResource|JsonResponse
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        if ($profile === null || $profile->pdf_url === null) {
            return response()->json(['message' => 'No hay PDF de catálogo para eliminar.'], 404);
        }

        $this->catalogMode->clearPdf($profile);

        return SellerAccountResource::make(
            $user->fresh()->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages'])
        );
    }

    public function destroyExcel(Request $request): SellerAccountResource|JsonResponse
    {
        $user = $request->user();
        $profile = $user->sellerProfile;

        if ($profile === null || $profile->excel_url === null) {
            return response()->json(['message' => 'No hay Excel de catálogo para eliminar.'], 404);
        }

        $this->catalogMode->clearExcel($profile);

        return SellerAccountResource::make(
            $user->fresh()->load(['sellerProfile.businessCategory', 'sellerProfile.catalogImages'])
        );
    }

    /**
     * @return array<string, array<string, mixed>|null>
     */
    private static function describeUploadedFiles(Request $request): array
    {
        $meta = [];

        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $index => $nested) {
                    $meta["{$key}.{$index}"] = self::describeSingleFile($nested);
                }

                continue;
            }

            $meta[$key] = self::describeSingleFile($file);
        }

        return $meta;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function describeSingleFile(mixed $file): ?array
    {
        if ($file === null) {
            return null;
        }

        return [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError(),
        ];
    }
}
