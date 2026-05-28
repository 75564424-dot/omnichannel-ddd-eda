<?php

declare(strict_types=1);

namespace App\Platform\Demo;

/**
 * Ejemplo de listener invocado por el bus de Laravel cuando llega el tipo
 * {@see DemoPackEventConsumers}. Sin lógica de dominio — referencia para packs reales.
 */
final class DemoPackListener
{
    /**
     * Compatible con eventos string: `Event::dispatch($type, [$envelope])`.
     *
     * @param  array<int, mixed>  $payload
     */
    public function handle(string $event, array $payload = []): void
    {
        // Intencionalmente vacío. Sustituir por integración real del pack.
    }
}
