<?php

declare(strict_types=1);

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->index(['tenant_id', 'platform_role']);
        });

        $this->backfillTenantIds();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'platform_role']);
            $table->dropColumn('tenant_id');
        });
    }

    private function backfillTenantIds(): void
    {
        if (! Schema::hasTable('tenants')) {
            return;
        }

        User::query()
            ->where('platform_role', PlatformRole::SaasAdmin->value)
            ->update(['tenant_id' => null]);

        TenantModel::query()->orderBy('id')->each(function (TenantModel $tenant): void {
            $settings = is_array($tenant->settings) ? $tenant->settings : [];
            $email = $settings['primary_admin_email'] ?? null;
            if (! is_string($email) || $email === '') {
                return;
            }

            User::query()
                ->where('email', $email)
                ->where('platform_role', '!=', PlatformRole::SaasAdmin->value)
                ->update(['tenant_id' => $tenant->id]);
        });

        $instanceRoles = [
            PlatformRole::PlatformAdmin->value,
            PlatformRole::BusOperator->value,
            PlatformRole::DashboardViewer->value,
        ];

        $slug = \Illuminate\Support\Str::slug((string) config('platform.client_slug', ''));
        if ($slug !== '') {
            $instanceTenant = TenantModel::query()->where('slug', $slug)->first();
            if ($instanceTenant !== null) {
                User::query()
                    ->whereNull('tenant_id')
                    ->whereIn('platform_role', $instanceRoles)
                    ->update(['tenant_id' => $instanceTenant->id]);
            }
        }
    }
};
