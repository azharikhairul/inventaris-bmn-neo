<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multisatker Mode Configuration
    |--------------------------------------------------------------------------
    | Jika disetel ke false, sistem akan beroperasi seperti sebelumnya (single satker)
    | tanpa filter satker dan tanpa switcher satker.
    */
    'enabled' => env('MULTISATKER_ENABLED', true),

    /*
    | Kode satker default untuk BPS Provinsi Gorontalo
    */
    'default_kode_satker' => env('DEFAULT_KODE_SATKER', '054013100636900000KD'),
];
