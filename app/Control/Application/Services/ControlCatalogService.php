<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

/**
 * Catálogo comercial (planes y módulos) para provisioning y venta SaaS.
 */
final class ControlCatalogService
{
    /** @return list<array<string, mixed>> */
    public function plansForSale(): array
    {
        $plans = config('saas_catalog.plans', []);
        if (! is_array($plans)) {
            return [];
        }

        $out = [];
        foreach ($plans as $key => $meta) {
            if (! is_array($meta)) {
                continue;
            }
            $out[] = array_merge(['id' => (string) $key], $meta);
        }

        return $out;
    }

    /** @return list<string> */
    public function planKeysForSale(): array
    {
        return array_map(
            static fn (array $p) => (string) $p['id'],
            $this->plansForSale(),
        );
    }

    /** @return list<array<string, mixed>> */
    public function modulesForSale(): array
    {
        $modules = config('saas_catalog.modules', []);
        if (! is_array($modules)) {
            return [];
        }

        $out = [];
        foreach ($modules as $key => $meta) {
            if (! is_array($meta)) {
                continue;
            }
            $out[] = array_merge(['id' => (string) $key], $meta);
        }

        return $out;
    }

    /** @return list<string> */
    public function moduleKeys(): array
    {
        return array_map(
            static fn (array $m) => (string) $m['id'],
            $this->modulesForSale(),
        );
    }

    /** @return list<string> */
    public function modulesIncludedInPlan(string $planId): array
    {
        $plans = config('saas_catalog.plans', []);
        if (! is_array($plans[$planId] ?? null)) {
            return ['middleware'];
        }

        $included = $plans[$planId]['modules_included'] ?? ['middleware'];

        return is_array($included) ? array_values(array_map('strval', $included)) : ['middleware'];
    }

    /** @return array<string, string> */
    public function industries(): array
    {
        $items = config('saas_catalog.industries', []);

        return is_array($items) ? $items : [];
    }

    /** @return list<string> */
    public function allPlanKeys(): array
    {
        return array_merge($this->planKeysForSale(), ['unconfigured']);
    }
}
