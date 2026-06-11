<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Support;

/**
 * Normalizes validated provisioning input into tenant profile and module lists.
 */
final class ProvisionNewTenantInputMapper
{
    /**
     * @param array<string, mixed> $validated
     *
     * @return array{profile: array<string, mixed>, modules: list<string>}
     */
    public function map(array $validated): array
    {
        $profile = array_filter([
            'legal_name' => $validated['legal_name'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'billing_email' => $validated['billing_email'] ?? null,
            'website' => $validated['website'] ?? null,
            'timezone' => $validated['timezone'] ?? 'UTC',
            'notes' => $validated['notes'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');

        $modules = array_values(array_unique($validated['modules']));
        if (! in_array('middleware', $modules, true)) {
            $modules[] = 'middleware';
        }

        return [
            'profile' => $profile,
            'modules' => $modules,
        ];
    }
}
