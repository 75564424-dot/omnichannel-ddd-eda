<?php

declare(strict_types=1);

use Illuminate\Support\Str;

$sessionCookie = env(
    'SESSION_COOKIE',
    Str::slug((string) env('APP_NAME', 'laravel'), '_').'_session',
);

$xsrfCookie = env('SESSION_XSRF_COOKIE');
if ($xsrfCookie === null || $xsrfCookie === '') {
    $xsrfCookie = str_contains($sessionCookie, '_session')
        ? str_replace('_session', '_xsrf', $sessionCookie)
        : 'XSRF-TOKEN';
}

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => $sessionCookie,

    'xsrf_cookie' => $xsrfCookie,

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
