<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Tenants;

use App\Control\Application\Services\ControlCatalogService;
use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Maps tenants and related DB facts for the SaaS control plane (no mock data).
 */
final class TenantPresentationService
{
    public function __construct(
        private readonly ControlCatalogService $catalog,
    ) {}
    /** @return list<array<string, mixed>> */
    public function listTenants(): array
    {
        return TenantModel::query()
            ->orderBy('name')
            ->get()
            ->map(fn (TenantModel $t) => $this->toSummary($t))
            ->values()
            ->all();
    }

    /** @return array<string, mixed>|null */
    public function tenantDetail(string $tenantId): ?array
    {
        $tenant = TenantModel::query()->find($tenantId);
        if ($tenant === null) {
            return null;
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];

        $profile = is_array($settings['company_profile'] ?? null) ? $settings['company_profile'] : [];
        $limits = is_array($settings['module_limits'] ?? null) ? $settings['module_limits'] : [];
        $catalog = is_array($settings['modules_catalog'] ?? null) ? $settings['modules_catalog'] : [];

        return array_merge($this->toSummary($tenant), [
            'plan'              => (string) ($settings['plan'] ?? 'unconfigured'),
            'modules'           => is_array($settings['modules'] ?? null) ? $settings['modules'] : [],
            'company_profile'   => $profile,
            'module_limits'     => $limits,
            'modules_catalog'   => [
                'producers_count'   => is_array($catalog['producers'] ?? null) ? count($catalog['producers']) : 0,
                'subscribers_count' => is_array($catalog['subscribers'] ?? null) ? count($catalog['subscribers']) : 0,
            ],
            'consumption'       => $this->consumptionForTenant($tenant->id),
            'primary_operator'  => $this->primaryOperatorForTenant($tenant),
            'operators'         => $this->tenantOperators($tenant->id),
        ]);
    }

    /** @return array<string, mixed> */
    private function toSummary(TenantModel $tenant): array
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];

        $catalog = is_array($settings['modules_catalog'] ?? null) ? $settings['modules_catalog'] : [];

        return [
            'id'         => $tenant->id,
            'slug'       => $tenant->slug,
            'name'       => $tenant->name,
            'status'     => $tenant->status,
            'app_url'    => $settings['app_url'] ?? null,
            'plan'       => (string) ($settings['plan'] ?? 'unconfigured'),
            'modules_catalog' => [
                'producers_count'   => is_array($catalog['producers'] ?? null) ? count($catalog['producers']) : 0,
                'subscribers_count' => is_array($catalog['subscribers'] ?? null) ? count($catalog['subscribers']) : 0,
            ],
            'created_at' => $tenant->created_at?->toDateTimeString(),
            'updated_at' => $tenant->updated_at?->toDateTimeString(),
        ];
    }

    /** @return array<string, int> */
    public function consumptionForTenant(string $tenantId): array
    {
        $counts = [
            'events_24h'      => 0,
            'queue_pending'   => 0,
            'dead_letters'    => 0,
            'event_logs'      => 0,
        ];

        if (Schema::hasTable('message_queue')) {
            $counts['queue_pending'] = (int) DB::table('message_queue')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'processing', 'PENDING', 'PROCESSING'])
                ->count();

            $counts['events_24h'] = (int) DB::table('message_queue')
                ->where('tenant_id', $tenantId)
                ->where('published_at', '>=', now()->subDay())
                ->count();
        }

        if (Schema::hasTable('dead_letter_queue')) {
            $counts['dead_letters'] = (int) DB::table('dead_letter_queue')
                ->where('tenant_id', $tenantId)
                ->whereNull('resolved_at')
                ->count();
        }

        if (Schema::hasTable('event_logs')) {
            $counts['event_logs'] = (int) DB::table('event_logs')
                ->where('tenant_id', $tenantId)
                ->where('logged_at', '>=', now()->subDay())
                ->count();
        }

        return $counts;
    }

    /** @return array<string, mixed>|null */
    private function primaryOperatorForTenant(TenantModel $tenant): ?array
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $email = $settings['primary_admin_email'] ?? config('platform_auth.admin_operator.email');

        if (! is_string($email) || $email === '') {
            return null;
        }

        $user = User::query()
            ->where('email', $email)
            ->where('tenant_id', $tenant->id)
            ->first(['id', 'name', 'email', 'platform_role']);

        return $user !== null ? [
            'id'            => $user->getKey(),
            'name'          => $user->getAttribute('name'),
            'email'         => $user->getAttribute('email'),
            'platform_role' => $user->getAttribute('platform_role'),
        ] : null;
    }

    /** @return list<array<string, mixed>> */
    public function tenantOperators(string $tenantId): array
    {
        $roles = array_map(
            fn (PlatformRole $r) => $r->value,
            array_filter(PlatformRole::cases(), fn (PlatformRole $r) => $r->isInstanceOperator()),
        );

        return User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('platform_role', $roles)
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'platform_role', 'created_at'])
            ->map(fn (User $u) => [
                'id'            => $u->getKey(),
                'name'          => $u->getAttribute('name'),
                'email'         => $u->getAttribute('email'),
                'platform_role' => $u->getAttribute('platform_role'),
                'created_at'    => $u->getAttribute('created_at')?->toDateTimeString(),
            ])
            ->all();
    }

    /** @return list<string> */
    public function availableModuleKeys(): array
    {
        return $this->catalog->moduleKeys();
    }

    /** @return list<string> */
    public function availablePlans(): array
    {
        return $this->catalog->allPlanKeys();
    }
}

