<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Redirects /{tenant_slug}/{path} requests to the silo's port-based URL.
 *
 * This implements ADR-011 Opción A (redirect): the control plane resolves
 * the silo URL and returns a 302. The browser navigates to the silo's URL.
 * Sessions remain isolated per silo process (no cross-process coupling).
 *
 * @see docs/production/ADR_011_friendly_routing_multitenant.md §Estrategia de proxy
 */
final class TenantPortalProxyController
{
    public function redirect(Request $request): RedirectResponse
    {
        $siloUrl  = (string) $request->attributes->get('resolved_silo_url', '');
        $slug     = (string) $request->route('tenant_slug', '');
        $subPath  = (string) $request->route('path', '');

        if ($siloUrl === '') {
            abort(503, 'Silo URL not resolved.');
        }

        if ($subPath === '') {
            $fullPath  = ltrim($request->path(), '/');
            $prefix    = $slug . '/';
            $extracted = Str::after($fullPath, $prefix);
            // Str::after returns the original string if needle is not found.
            // That means we are on the root /{tenant_slug} path with no subpath.
            $subPath = ($extracted !== $fullPath) ? $extracted : '';
        }

        $subPath = ltrim($subPath, '/');

        if ($subPath === '') {
            $subPath = 'login';
        }

        return redirect($siloUrl . '/' . $subPath, 302);
    }
}
