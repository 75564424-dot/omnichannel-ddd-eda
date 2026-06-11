<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\Support\MiddlewarePlatformAuthorizer;
use App\Middleware\Application\UseCases\SyncConfiguredModulesToRegistryUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Writes catalog-defined producers/consumers into the persistent module registry.
 */
final class ModuleRegistrySyncController
{
    public function __construct(
        private readonly SyncConfiguredModulesToRegistryUseCase $syncConfiguredModules,
        private readonly MiddlewarePlatformAuthorizer $authorizer,
    ) {}

    /**
     * POST /api/middleware/registry/sync-config
     */
    public function syncFromConfig(): JsonResponse
    {
        $this->authorizer->authorizeSyncRegistry();

        $stats = $this->syncConfiguredModules->execute();

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }
}
