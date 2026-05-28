<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

/**
 * Valida coherencia entre catálogo declarativo (modules_config.json) y routing eventbus.php.
 * Regla B.3: lo declarado en JSON debe estar suscrito/configurado en eventbus.
 */
final class PlatformCatalogValidator
{
    /**
     * @return list<string> Mensajes de error (vacío = válido).
     */
    public function validate(): array
    {
        $errors = [];

        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            return ['modules.catalog is not an array'];
        }

        $eventbusProducers = config('eventbus.producers', []);
        $eventbusSubscriptions = config('eventbus.subscriptions', []);

        if (! is_array($eventbusProducers)) {
            $errors[] = 'eventbus.producers is not an array';
            $eventbusProducers = [];
        }

        if (! is_array($eventbusSubscriptions)) {
            $errors[] = 'eventbus.subscriptions is not an array';
            $eventbusSubscriptions = [];
        }

        $producers = isset($catalog['producers']) && is_array($catalog['producers'])
            ? array_values($catalog['producers'])
            : [];

        foreach ($producers as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $types = $row['event_types_emitted'] ?? [];
            if (! is_array($types)) {
                continue;
            }

            $busEntry = $eventbusProducers[$id] ?? null;
            if ($busEntry === null) {
                $errors[] = "Producer \"{$id}\" declared in modules_config but missing in eventbus.producers";

                continue;
            }

            $produces = is_array($busEntry['produces'] ?? null) ? $busEntry['produces'] : [];
            $producesNormalized = array_map('strval', $produces);

            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }
                if (! in_array($eventType, $producesNormalized, true)) {
                    $errors[] = "Producer \"{$id}\": event \"{$eventType}\" declared in modules_config but not listed in eventbus.producers[{$id}].produces";
                }
            }
        }

        $subscribers = isset($catalog['subscribers']) && is_array($catalog['subscribers'])
            ? array_values($catalog['subscribers'])
            : [];

        foreach ($subscribers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $types = $row['event_types_consumed'] ?? [];
            if (! is_array($types)) {
                continue;
            }

            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }

                $subscribersForEvent = $eventbusSubscriptions[$eventType] ?? null;
                if ($subscribersForEvent === null) {
                    $errors[] = "Subscriber \"{$id}\": event \"{$eventType}\" declared in modules_config but missing in eventbus.subscriptions";

                    continue;
                }

                if (! is_array($subscribersForEvent)) {
                    $errors[] = "eventbus.subscriptions[\"{$eventType}\"] must be an array";

                    continue;
                }

                $moduleIds = [];
                foreach ($subscribersForEvent as $binding) {
                    if (is_array($binding) && isset($binding['module'])) {
                        $moduleIds[] = strtolower(trim((string) $binding['module']));
                    } elseif (is_string($binding)) {
                        $moduleIds[] = strtolower(trim($binding));
                    }
                }

                if (! in_array(strtolower($id), $moduleIds, true)) {
                    $errors[] = "Subscriber \"{$id}\": event \"{$eventType}\" declared in modules_config but not subscribed in eventbus.subscriptions";
                }
            }
        }

        return $errors;
    }
}
