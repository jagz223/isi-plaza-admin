<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Contracts\MediaStorage;
use App\Enums\AccessStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SellerDocumentController extends Controller
{
    public function __construct(private MediaStorage $mediaStorage) {}

    public function pdf(User $seller): StreamedResponse
    {
        return $this->streamDocument($seller, 'pdf', 'catalogo.pdf', 'application/pdf');
    }

    public function excel(User $seller): StreamedResponse
    {
        return $this->streamDocument($seller, 'excel', 'catalogo.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function streamDocument(
        User $seller,
        string $field,
        string $downloadName,
        string $contentType,
    ): StreamedResponse {
        abort_unless($seller->role === UserRole::Mayorista, 404);

        $profile = $seller->sellerProfile;

        if ($profile === null || $profile->access_status !== AccessStatus::Active) {
            abort(404);
        }

        $storedUrl = $field === 'pdf' ? $profile->pdf_url : $profile->excel_url;

        if ($storedUrl === null || $storedUrl === '') {
            abort(404);
        }

        try {
            $stream = $this->mediaStorage->readStream($storedUrl);
            $resolvedType = $this->mediaStorage->contentTypeForStoredValue($storedUrl);
        } catch (Throwable $exception) {
            Log::warning("consumer.sellers.{$field}: failed", [
                'seller_id' => $seller->id,
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
            'Content-Type' => $resolvedType !== 'application/octet-stream' ? $resolvedType : $contentType,
            'Content-Disposition' => 'attachment; filename="'.$downloadName.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
