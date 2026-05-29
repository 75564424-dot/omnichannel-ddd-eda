<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\AuditControlPlaneMiddleware;
use App\Http\Middleware\AuthenticatePlatformApi;
use App\Http\Middleware\EnsureControlWebAuth;
use App\Http\Middleware\EnsureControlPlaneHost;
use App\Http\Middleware\EnsureInstancePortalAccess;
use App\Http\Middleware\EnsureInstanceWebAuth;
use App\Http\Middleware\EnsurePlatformWebAuth;
use App\Http\Middleware\EnsurePlatformRole;
use App\Http\Middleware\EnsureSimulationInternalRequest;
use App\Http\Middleware\EnforcePlatformAbility;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Providers\Registrars\PlatformRateLimitConfigurator;
use App\Shared\Security\Contracts\AuditLogWriterInterface;
use App\Shared\Security\Contracts\PlatformApiAuthenticatorInterface;
use App\Shared\Security\Services\AuditLogWriter;
use App\Shared\Security\Services\PlatformApiAuthenticator;
use Illuminate\Support\ServiceProvider;

final class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlatformApiAuthenticatorInterface::class, PlatformApiAuthenticator::class);
        $this->app->singleton(AuditLogWriterInterface::class, AuditLogWriter::class);
    }

    public function boot(): void
    {
        PlatformRateLimitConfigurator::configure();
    }

    /**
     * @return array<string, class-string>
     */
    public static function middlewareAliases(): array
    {
        return [
            'auth.platform' => AuthenticatePlatformApi::class,
            'auth.platform.web' => EnsurePlatformWebAuth::class,
            'control.plane' => EnsureControlPlaneHost::class,
            'simulation.internal' => EnsureSimulationInternalRequest::class,
            'control.web' => EnsureControlWebAuth::class,
            'instance.web' => EnsureInstanceWebAuth::class,
            'instance.portal' => EnsureInstancePortalAccess::class,
            'platform.role' => EnsurePlatformRole::class,
            'platform.ability' => EnforcePlatformAbility::class,
            'platform.audit' => AuditControlPlaneMiddleware::class,
            'security.headers' => SecurityHeadersMiddleware::class,
        ];
    }
}
