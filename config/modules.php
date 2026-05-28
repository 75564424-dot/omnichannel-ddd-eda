<?php

declare(strict_types=1);

/**
 * Catálogo declarativo de productores/suscriptores conectados al bus (solo lectura para el dashboard).
 *
 * Edición: config/modules/modules_config.json — la UI no modifica este archivo.
 */

$path = __DIR__.'/modules/modules_config.json';

$defaults = [
    'middleware' => [
        'id'          => 'middleware',
        'name'        => 'Middleware bus',
        'description' => 'Ingesta y distribución de eventos según el catálogo del bus.',
        'role'        => 'routing',
    ],
    'producers'   => [],
    'subscribers' => [],
];

$message = 'Para agregar nuevos módulos, contacte con el proveedor del servicio.';

$catalog = $defaults;

if (is_readable($path)) {
    try {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($decoded)) {
            if (isset($decoded['service_contact_message']) && is_string($decoded['service_contact_message'])) {
                $message = $decoded['service_contact_message'];
            }
            if (isset($decoded['middleware']) && is_array($decoded['middleware'])) {
                $catalog['middleware'] = array_merge($catalog['middleware'], $decoded['middleware']);
            }
            $catalog['producers'] = isset($decoded['producers']) && is_array($decoded['producers'])
                ? array_values($decoded['producers'])
                : [];
            $catalog['subscribers'] = isset($decoded['subscribers']) && is_array($decoded['subscribers'])
                ? array_values($decoded['subscribers'])
                : [];
        }
    } catch (\JsonException) {
        // conservar defaults
    }
}

return [
    'catalog'                  => $catalog,
    'service_contact_message'=> $message,
];
