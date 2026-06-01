<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface MediaStorage
{
    /**
     * Sube un archivo y devuelve la URL pública de descarga (Firebase Storage).
     */
    public function uploadUploadedFile(UploadedFile $file, string $objectPath): string;

    /**
     * Elimina el objeto asociado a una URL o ruta legacy en disco local.
     */
    public function deleteByStoredValue(?string $storedValue): void;

    /**
     * Resuelve URL para la API (URL completa o ruta legacy en storage/public).
     */
    public function resolveUrl(?string $storedValue): ?string;
}
