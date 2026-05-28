<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Branding and live-module rows for the client (instance) portal, derived from the deployed tenant.
 */
final class ClientInstancePortalService
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
        private readonly TenantModuleCatalogService $catalogService,
    ) {}

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

    /** @return array{company_name: string, company_slug: string} */
    public function branding(): array
    {
        $tenant = $this->resolveTenant();

        return [
            'company_name' => $tenant !== null
                ? (string) $tenant->name
                : $this->instanceContext->clientName(),
            'company_slug' => $this->instanceContext->clientSlug(),
        ];
    }

    /**
     * Rows for the Live panel and dashboard node API (keys must match {@see self::monitoredNodeKeys()}).
     *
     * @return list<array{key: string, label: string, kind: string, description: string}>
     */
    public function liveModuleRows(): array
    {
        $tenant = $this->resolveTenant();
        if ($tenant === null) {
            return $this->fallbackLiveRows();
        }

        $catalog = $this->catalogService->getCatalog($tenant);
        $rows    = [];

        $middleware = is_array($catalog['middleware'] ?? null) ? $catalog['middleware'] : [];
        $rows[] = [
            'key'         => 'middleware',
            'label'       => trim((string) ($middleware['name'] ?? '')) ?: 'Middleware bus',
            'kind'        => 'middleware',
            'description' => trim((string) ($middleware['description'] ?? ''))
                ?: 'Bus central de eventos de su instancia.',
        ];

        foreach (is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [] as $producer) {
            if (! is_array($producer)) {
                continue;
            }
            $id = trim((string) ($producer['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $rows[] = [
                'key'         => self::producerNodeKey($id),
                'label'       => trim((string) ($producer['name'] ?? $id)),
                'kind'        => 'producer',
                'description' => 'Productor configurado para su empresa.',
            ];
        }

        foreach (is_array($catalog['subscribers'] ?? null) ? $catalog['subscribers'] : [] as $subscriber) {
            if (! is_array($subscriber)) {
                continue;
            }
            $id = trim((string) ($subscriber['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $rows[] = [
                'key'         => self::subscriberNodeKey($id),
                'label'       => trim((string) ($subscriber['name'] ?? $id)),
                'kind'        => 'subscriber',
                'description' => 'Suscriptor configurado para su empresa.',
            ];
        }

        return $rows !== [] ? $rows : $this->fallbackLiveRows();
    }

    /** @return list<string> */
    public function monitoredNodeKeys(): array
    {
        $fromConfig = config('dashboard.monitored_node_keys', ['middleware']);
        $fromConfig = is_array($fromConfig) ? $fromConfig : ['middleware'];

        $keys = [];
        foreach ($fromConfig as $key) {
            $keys[] = (string) $key;
        }

        foreach ($this->liveModuleRows() as $row) {
            $keys[] = (string) $row['key'];
        }

        return array_values(array_unique($keys));
    }

    /** @return array<string, mixed> */
    public function sharedForInertia(): array
    {
        $branding = $this->branding();

        return [
            'company_name' => $branding['company_name'],
            'company_slug' => $branding['company_slug'],
            'live_modules' => $this->liveModuleRows(),
        ];
    }

    public static function producerNodeKey(string $producerId): string
    {
        return 'producer:'.trim($producerId);
    }

    public static function subscriberNodeKey(string $subscriberId): string
    {
        return 'subscriber:'.trim($subscriberId);
    }

    /** @return list<array{key: string, label: string, kind: string, description: string}> */
    private function fallbackLiveRows(): array
    {
        return [
            [
                'key'         => 'middleware',
                'label'       => 'Middleware bus',
                'kind'        => 'middleware',
                'description' => 'Bus central de eventos.',
            ],
        ];
    }
}
