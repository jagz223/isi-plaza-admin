<?php

return [

    'default_radius_km' => (float) env('ODONTICA_GEO_RADIUS_KM', 20),

    'max_radius_km' => 100,

    'regions' => [
        'cdmx' => [
            'label' => 'Ciudad de México',
            'state_names' => [
                'Ciudad de México',
                'Ciudad de Mexico',
                'CDMX',
                'Distrito Federal',
            ],
            'municipalities' => [
                'Álvaro Obregón',
                'Azcapotzalco',
                'Benito Juárez',
                'Coyoacán',
                'Cuajimalpa de Morelos',
                'Cuauhtémoc',
                'Gustavo A. Madero',
                'Iztacalco',
                'Iztapalapa',
                'La Magdalena Contreras',
                'Miguel Hidalgo',
                'Milpa Alta',
                'Tláhuac',
                'Tlalpan',
                'Venustiano Carranza',
                'Xochimilco',
            ],
        ],
        'edo_mex' => [
            'label' => 'Estado de México',
            'state_names' => [
                'Estado de México',
                'Estado de Mexico',
                'Edomex',
                'México',
            ],
            'municipalities' => [
                'Ecatepec de Morelos',
                'Nezahualcóyotl',
                'Naucalpan de Juárez',
                'Tlalnepantla de Baz',
                'Atizapán de Zaragoza',
                'Cuautitlán Izcalli',
                'Toluca',
                'Chimalhuacán',
                'Tultitlán',
                'Coacalco de Berriozábal',
                'Texcoco',
                'Ixtapaluca',
                'Chalco',
                'Nicolás Romero',
                'Huixquilucan',
                'Tecámac',
                'Zumpango',
                'Valle de Chalco Solidaridad',
                'La Paz',
                'Metepec',
            ],
        ],
    ],

];
