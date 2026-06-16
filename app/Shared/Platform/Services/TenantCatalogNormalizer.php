<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

final class TenantCatalogNormalizer
{
    /**
     * @param array<string, mixed> $catalog
     *
     * @return array{
     *     middleware: array<string, mixed>,
     *     producers: list<array<string, mixed>>,
     *     subscribers: list<array<string, mixed>>,
     *     service_contact_message?: string
     * }
     */
    public function normalize(array $catalog): array
    {
        $normalized = [
            'middleware'  => is_array($catalog['middleware'] ?? null) ? $catalog['middleware'] : [],
            'producers'   => is_array($catalog['producers'] ?? null) ? array_values($catalog['producers']) : [],
            'subscribers' => is_array($catalog['subscribers'] ?? null) ? array_values($catalog['subscribers']) : [],
        ];

        if (isset($catalog['service_contact_message']) && is_string($catalog['service_contact_message'])) {
            $normalized['service_contact_message'] = $catalog['service_contact_message'];
        }

        return $normalized;
    }
}
