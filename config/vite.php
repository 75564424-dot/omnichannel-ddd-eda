<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Manifest Path
    |--------------------------------------------------------------------------
    |
    | Path to the generated Vite manifest file. Laravel will use this to
    | resolve versioned assets. Adjust if your build output directory differs.
    |
    */
    'manifest_path' => public_path('build/manifest.json'),

    /*
    |--------------------------------------------------------------------------
    | Vite Hot File
    |--------------------------------------------------------------------------
    |
    | When running Vite in hot-reload mode, this file is created. Laravel
    | checks its existence to serve assets from the Vite dev server.
    |
    */
    'hot_file' => public_path('hot'),

    /*
    |--------------------------------------------------------------------------
    | Dev Server URL
    |--------------------------------------------------------------------------
    |
    | The URL of the Vite development server. If you change the port or
    | hostname, update this value accordingly.
    |
    */
    'dev_server' => [
        'url' => env('VITE_DEV_SERVER_URL', 'http://127.0.0.1:5173'),
        // Optional: set to true if you need TLS verification disabled (dev only)
        'https' => false,
    ],
];
?>
