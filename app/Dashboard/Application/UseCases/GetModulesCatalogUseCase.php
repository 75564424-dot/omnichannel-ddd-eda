<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Application\Contracts\ModulesCatalogDataProviderInterface;

/**
 * Expone el catálogo de módulos declarativos para la UI de topología (solo lectura).
 */
final class GetModulesCatalogUseCase
{
    public function __construct(
        private readonly ModulesCatalogDataProviderInterface $catalogDataProvider,
    ) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        return $this->catalogDataProvider->getPresentationCatalog();
    }
}
