<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App 2 — Mayorista (suscripción y promoción por WhatsApp)
    |--------------------------------------------------------------------------
    */

    'seller' => [
        'subscription_price_label' => env('ISI_PLAZA_SUBSCRIPTION_LABEL', 'Suscripción mensual de 100 MXN'),
        'subscription_whatsapp_url' => env('ISI_PLAZA_SUBSCRIPTION_WHATSAPP_URL', 'https://wa.me/5215500000000?text=Solicito%20suscripción%20ISI%20PLAZA'),
        'promotion_whatsapp_url' => env('ISI_PLAZA_PROMOTION_WHATSAPP_URL', 'https://wa.me/5215500000000?text=Solicito%20promoción%20banner%20ISI%20PLAZA'),
        'max_catalog_images' => 5,
        'catalog_carousel_count' => 5,
        'catalog_max_images_per_carousel' => 5,
        'catalog_max_images_total' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | App 1 — Comprador (filtros de país del PDF)
    |--------------------------------------------------------------------------
    */

    'consumer' => [
        'filter_countries' => [
            'China',
            'México',
            'Brasil',
            'Argentina',
            'Colombia',
            'Chile',
            'Perú',
            'Venezuela',
            'Ecuador',
            'Bolivia',
            'Paraguay',
            'Uruguay',
            'Guatemala',
            'Costa Rica',
        ],
        'sellers_per_page' => 20,
    ],

];
