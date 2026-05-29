<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\Services\Registry\ConfiguredModuleRegistrySyncService;

/**
 * Upserts registry rows from eventbus config and modules catalog.
 */
final class SyncConfiguredModulesToRegistryUseCase
{
    public function __construct(
        private readonly ConfiguredModuleRegistrySyncService $sync,
    ) {}

    /**
     * @return array{producer_bindings: int, consumer_bindings: int}
     */
    public function execute(): array
    {
        return $this->sync->sync();
    }
}
