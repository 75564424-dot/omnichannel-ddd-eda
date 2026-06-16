<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Infrastructure\Models\TenantModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves a tenant from a /{tenant_slug}/* route prefix and attaches
 * the silo's app_url to the request for the proxy controller.
 *
 * @see docs/production/ADR_011_friendly_routing_multitenant.md
 */
final class ResolveTenantFromRoutePath
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('platform.friendly_routing', false)) {
            abort(404);
        }

        $slug = $request->route('tenant_slug');

        if (! is_string($slug) || $slug === '') {
            abort(404);
        }

        $slug = Str::slug($slug);

        $tenant = TenantModel::query()->where('slug', $slug)->first();

        if ($tenant === null) {
            abort(404, "Tenant '{$slug}' not found.");
        }

        if ($tenant->status === 'suspended') {
            abort(503, "The service for '{$slug}' is temporarily suspended. Contact your SaaS administrator.");
        }

        $settings     = is_array($tenant->settings) ? $tenant->settings : [];
        $localInstance = $settings['deployment']['local_instance'] ?? null;

        if (! is_array($localInstance) || empty($localInstance['app_url'])) {
            abort(503, "No provisioned silo found for '{$slug}'. The instance has not been started yet.");
        }

        $request->attributes->set('resolved_silo_url', rtrim((string) $localInstance['app_url'], '/'));

        return $next($request);
    }
}
