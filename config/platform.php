<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documento Firestore (interruptor global)
    |--------------------------------------------------------------------------
    |
    | Crea en Firebase Console → Firestore:
    |   Colección: platform
    |   Documento: access
    |   Campo booleano: app_enabled = true
    |
    | Si app_enabled es false, la API y el panel web responden bloqueados.
    |
    */

    'firestore' => [
        'collection' => env('FIRESTORE_ACCESS_COLLECTION', 'platform'),
        'document' => env('FIRESTORE_ACCESS_DOCUMENT', 'access'),
        'field' => env('FIRESTORE_ACCESS_FIELD', 'app_enabled'),
    ],

    /** Si no existe el documento o falla la lectura, ¿permitir acceso? */
    'fail_open' => env('PLATFORM_ACCESS_FAIL_OPEN', true),

    /** Segundos de caché en Laravel (0 = sin caché). */
    'cache_seconds' => (int) env('PLATFORM_ACCESS_CACHE_SECONDS', 5),

];
