<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Dashboard\Infrastructure\Modules\ConfigModulesCatalogDataProvider;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

/**
 * Catálogo SaaS (tenant) vs módulos visibles en la topología del dashboard del cliente.
 */
final class ClientDashboardModulesService
{
    private const SETTINGS_KEY = 'dashboard_visible_modules';

    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
        private readonly TenantModuleCatalogService $catalogService,
        private readonly ConfigModulesCatalogDataProvider $catalogNormalizer,
    ) {}

    /** @return array<string, mixed> */
    public function presentationCatalog(): array
    {
        $saas = $this->saasCatalog();
        $visible = $this->visibleModuleIds();

        return [
            'middleware'              => $saas['middleware'],
            'producers'               => $this->filterModules($saas['producers'], $visible['producers']),
            'subscribers'             => $this->filterModules($saas['subscribers'], $visible['subscribers']),
            'available_producers'     => $saas['producers'],
            'available_subscribers'   => $saas['subscribers'],
            'visible_producer_ids'    => $visible['producers'],
            'visible_subscriber_ids'  => $visible['subscribers'],
            'service_contact_message' => $saas['service_contact_message'],
        ];
    }

    /**
     * @param list<string> $producerIds
     * @param list<string> $subscriberIds
     */
    public function updateVisibleModules(array $producerIds, array $subscriberIds): void
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            throw new RuntimeException('No hay tenant de instancia para guardar la visibilidad de módulos.');
        }

        $saas = $this->saasCatalog();
        $allowedProducers = $this->moduleIdSet($saas['producers']);
        $allowedSubscribers = $this->moduleIdSet($saas['subscribers']);

        $producers = $this->validateIdSubset($producerIds, $allowedProducers, 'productor');
        $subscribers = $this->validateIdSubset($subscriberIds, $allowedSubscribers, 'suscriptor');

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings[self::SETTINGS_KEY] = [
            'producers'   => $producers,
            'subscribers' => $subscribers,
            'updated_at'  => now()->toIso8601String(),
        ];

        $tenant->update(['settings' => $settings]);
    }

    /** @return array{producers: list<string>, subscribers: list<string>} */
    private function visibleModuleIds(): array
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return ['producers' => [], 'subscribers' => []];
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $stored = $settings[self::SETTINGS_KEY] ?? null;
        if (! is_array($stored)) {
            return ['producers' => [], 'subscribers' => []];
        }

        return [
            'producers'   => $this->stringIdList($stored['producers'] ?? []),
            'subscribers' => $this->stringIdList($stored['subscribers'] ?? []),
        ];
    }

    /** @return array<string, mixed> */
    private function saasCatalog(): array
    {
        $tenant = $this->resolveTenant();
        if ($tenant !== null) {
            $raw = $this->catalogService->getCatalog($tenant);
            if ($this->catalogHasModules($raw)) {
                return $this->catalogNormalizer->normalizeCatalogArray($raw);
            }
        }

        return $this->catalogNormalizer->getPresentationCatalog();
    }

    /** @param array<string, mixed> $raw */
    private function catalogHasModules(array $raw): bool
    {
        $producers = is_array($raw['producers'] ?? null) ? $raw['producers'] : [];
        $subscribers = is_array($raw['subscribers'] ?? null) ? $raw['subscribers'] : [];

        return $producers !== [] || $subscribers !== [];
    }

    public function resolveTenant(): ?TenantModel
    {
        if (! Schema::hasTable('tenants')) {
            return null;
        }

        $slug = strtolower(trim($this->instanceContext->clientSlug()));
        if ($slug === '') {
            return null;
        }

        return TenantModel::query()->where('slug', $slug)->first();
    }

    /**
     * @param list<array<string, mixed>> $modules
     * @param list<string>              $visibleIds
     *
     * @return list<array<string, mixed>>
     */
    private function filterModules(array $modules, array $visibleIds): array
    {
        if ($visibleIds === []) {
            return [];
        }

        $allowed = array_flip($visibleIds);
        $out = [];
        foreach ($modules as $module) {
            $id = trim((string) ($module['id'] ?? ''));
            if ($id !== '' && isset($allowed[$id])) {
                $out[] = $module;
            }
        }

        return $out;
    }

    /** @param list<array<string, mixed>> $modules */
    private function moduleIdSet(array $modules): array
    {
        $set = [];
        foreach ($modules as $module) {
            $id = trim((string) ($module['id'] ?? ''));
            if ($id !== '') {
                $set[$id] = true;
            }
        }

        return $set;
    }

    /**
     * @param list<string>    $requested
     * @param array<string, true> $allowed
     *
     * @return list<string>
     */
    private function validateIdSubset(array $requested, array $allowed, string $label): array
    {
        $out = [];
        foreach ($this->stringIdList($requested) as $id) {
            if (! isset($allowed[$id])) {
                throw new InvalidArgumentException("El {$label} «{$id}» no está configurado para su empresa.");
            }
            $out[] = $id;
        }

        return array_values(array_unique($out));
    }

    /** @return list<string> */
    private function stringIdList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $list = [];
        foreach ($raw as $item) {
            $id = trim((string) $item);
            if ($id !== '') {
                $list[] = $id;
            }
        }

        return array_values(array_unique($list));
    }
}
