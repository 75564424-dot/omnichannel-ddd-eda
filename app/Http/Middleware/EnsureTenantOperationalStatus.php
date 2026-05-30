<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantOperationalStatus
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip on the SaaS Control Plane (we always want to access administration tools)
        if (config('platform.control_plane', false)) {
            return $next($request);
        }

        // 2. Skip on health checking or assets path
        $path = $request->path();
        if ($path === 'up' || $path === 'health/ready' || str_starts_with($path, '_vite') || str_starts_with($path, 'build')) {
            return $next($request);
        }

        // 3. Resolve tenant in Silo database
        $tenantId = $this->instanceContext->tenantId();
        if ($tenantId === null) {
            return $next($request);
        }

        $tenant = TenantModel::query()->find($tenantId);
        if ($tenant !== null && $tenant->status === 'suspended') {
            // Force session logout if authenticated
            if ($request->user() !== null) {
                auth()->logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            // A. API Response in Problem Details format
            if ($request->is('api/*') || $request->expectsJson()) {
                return ProblemDetailsFactory::make(
                    title: 'Tenant Suspended',
                    status: 403,
                    detail: 'El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador para obtener más información.',
                    type: 'tenant_suspended'
                );
            }

            // B. Beautiful Premium 503 Web View response
            return new Response($this->suspendedHtmlView(), 503);
        }

        return $next($request);
    }

    private function suspendedHtmlView(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio Suspendido | Omnichannel Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --card-bg: rgba(30, 41, 59, 0.45);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-glow: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, rgba(99, 102, 241, 0.05) 100%);
            --indigo: #6366f1;
            --pink: #ec4899;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Glow Effects */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            top: -200px;
            left: -200px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.1) 0%, transparent 70%);
            bottom: -200px;
            right: -200px;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 10;
            max-width: 560px;
            width: 100%;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 40px rgba(99, 102, 241, 0.1);
            animation: floatCard 6s ease-in-out infinite;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(236, 72, 153, 0.1);
            border: 1px solid rgba(236, 72, 153, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 0 20px rgba(236, 72, 153, 0.15);
        }

        .icon-container svg {
            width: 40px;
            height: 40px;
            stroke: var(--pink);
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #ffffff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1.05rem;
            line-height: 1.6;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, var(--card-border), transparent);
            margin: 2rem 0;
        }

        .footer {
            font-size: 0.85rem;
            color: rgba(148, 163, 184, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .footer svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                </svg>
            </div>
            
            <h1>Servicio Suspendido</h1>
            
            <p>El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador de la plataforma para obtener más información.</p>
            
            <div class="divider"></div>
            
            <div class="footer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                </svg>
                <span>Acceso Seguro Protegido por Omnichannel Corp</span>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
