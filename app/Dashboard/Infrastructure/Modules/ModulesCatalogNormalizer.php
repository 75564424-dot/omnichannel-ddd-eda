<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Modules;

/**
 * Normalizes declarative module catalog arrays for dashboard presentation.
 */
final class ModulesCatalogNormalizer
{
    /**
     * @param array<string, mixed> $catalog
     *
     * @return array<string, mixed>
     */
    public function normalizeCatalogArray(array $catalog, ?string $serviceContactMessage = null): array
    {
        $msg = $serviceContactMessage
            ?? trim((string) ($catalog['service_contact_message'] ?? ''))
            ?: 'Para agregar nuevos módulos, contacte con el proveedor del servicio.';

        $middleware = is_array($catalog['middleware'] ?? null) ? $catalog['middleware'] : [];
        $producers  = isset($catalog['producers']) && is_array($catalog['producers'])
            ? array_values($catalog['producers'])
            : [];
        $subscribers = isset($catalog['subscribers']) && is_array($catalog['subscribers'])
            ? array_values($catalog['subscribers'])
            : [];

        return [
            'middleware'              => $this->normalizeMiddleware($middleware),
            'producers'               => $this->normalizeProducers($producers),
            'subscribers'             => $this->normalizeSubscribers($subscribers),
            'service_contact_message' => $msg,
        ];
    }

    /** @param list<array<string, mixed>> $rows */
    private function normalizeProducers(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id   = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $types = $row['event_types_emitted'] ?? [];
            $out[] = [
                'id'                    => $id,
                'name'                  => $name,
                'event_types_emitted'   => $this->stringList($types),
            ];
        }

        return $out;
    }

    /** @param list<array<string, mixed>> $rows */
    private function normalizeSubscribers(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id   = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $types = $row['event_types_consumed'] ?? [];
            $out[] = [
                'id'                     => $id,
                'name'                   => $name,
                'event_types_consumed'   => $this->stringList($types),
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $m */
    private function normalizeMiddleware(array $m): array
    {
        return [
            'id'          => trim((string) ($m['id'] ?? 'middleware')) ?: 'middleware',
            'name'        => trim((string) ($m['name'] ?? 'Middleware bus')) ?: 'Middleware bus',
            'description' => trim((string) ($m['description'] ?? '')),
            'role'        => trim((string) ($m['role'] ?? 'routing')) ?: 'routing',
        ];
    }

    /** @return list<string> */
    private function stringList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $list = [];
        foreach ($raw as $item) {
            $s = trim((string) $item);
            if ($s !== '') {
                $list[] = $s;
            }
        }

        return array_values(array_unique($list));
    }
}
