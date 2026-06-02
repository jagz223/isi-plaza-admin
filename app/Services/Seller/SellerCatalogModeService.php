<?php

namespace App\Services\Seller;

use App\Contracts\MediaStorage;
use App\Models\SellerProfile;

class SellerCatalogModeService
{
    public function __construct(private MediaStorage $mediaStorage) {}

    public function clearCarousel(SellerProfile $profile): void
    {
        $profile->loadMissing('catalogImages');

        foreach ($profile->catalogImages as $image) {
            $this->mediaStorage->deleteByStoredValue($image->image_url);
            $image->delete();
        }

        $profile->carousel_metadata = [];
        $profile->save();
    }

    public function clearPdf(SellerProfile $profile): void
    {
        $this->mediaStorage->deleteByStoredValue($profile->pdf_url);
        $profile->pdf_url = null;
        $profile->save();
    }

    public function clearExcel(SellerProfile $profile): void
    {
        $this->mediaStorage->deleteByStoredValue($profile->excel_url);
        $profile->excel_url = null;
        $profile->save();
    }

    public function applyPdfUpload(SellerProfile $profile, string $pdfUrl): void
    {
        $this->clearExcel($profile);
        $this->clearCarousel($profile);
        $profile->pdf_url = $pdfUrl;
        $profile->save();
    }

    public function applyExcelUpload(SellerProfile $profile, string $excelUrl): void
    {
        $this->clearPdf($profile);
        $this->clearCarousel($profile);
        $profile->excel_url = $excelUrl;
        $profile->save();
    }

    public function ensureCarouselAllowed(SellerProfile $profile): void
    {
        if ($profile->pdf_url || $profile->excel_url) {
            throw new \RuntimeException('Elimina el PDF o Excel antes de subir imágenes al carrusel.');
        }
    }
}
