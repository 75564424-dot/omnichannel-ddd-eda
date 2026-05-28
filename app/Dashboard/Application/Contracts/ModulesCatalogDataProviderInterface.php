<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Contracts;

/**
 * Fuente de verdad declarativa para el visualizador de topología (sin lógica de negocio).
 */
interface ModulesCatalogDataProviderInterface
{
    /**
     * @return array{
     *   producers: list<array<string, mixed>>,
     *   subscribers: list<array<string, mixed>>,
     *   middleware: array<string, mixed>,
     *   service_contact_message: string
     * }
     */
    public function getPresentationCatalog(): array;
}
