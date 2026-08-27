<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The Astro frontend is a static site on a different origin
    | (angelinvestor.pk, or new.angelinvestor.pk before cutover) than this
    | API, so every browser POST to /api/* is cross-origin and needs CORS
    | explicitly enabled — unlike faithfuture, which is same-origin and has
    | no cors.php at all.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', 'https://angelinvestor.pk')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
