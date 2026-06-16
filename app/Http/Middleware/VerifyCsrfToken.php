<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

final class VerifyCsrfToken extends Middleware
{
    /** @var list<string> */
    protected $except = [
        'control/internal/*',
    ];

    protected function getTokenFromRequest($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (! $token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = CookieValuePrefix::remove($this->encrypter->decrypt($header, static::serialized()));
            } catch (DecryptException) {
                // Per-instance XSRF cookies are not encrypted (EncryptCookies::except).
                $token = $header;
            }
        }

        return $token;
    }

    protected function addCookieToResponse($request, $response)
    {
        $response = parent::addCookieToResponse($request, $response);

        // Drop legacy shared-host cookie so Inertia/axios do not pick a token from another port.
        $config = config('session');
        $legacy = new Cookie(
            'XSRF-TOKEN',
            '',
            1,
            (string) ($config['path'] ?? '/'),
            $config['domain'] ?? null,
            (bool) ($config['secure'] ?? false),
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false,
        );
        $response->headers->setCookie($legacy);

        return $response;
    }

    protected function newCookie($request, $config): Cookie
    {
        return new Cookie(
            (string) ($config['xsrf_cookie'] ?? 'XSRF-TOKEN'),
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false,
        );
    }

    public static function serialized(): bool
    {
        return \Illuminate\Cookie\Middleware\EncryptCookies::serialized(
            (string) config('session.xsrf_cookie', 'XSRF-TOKEN'),
        );
    }
}
