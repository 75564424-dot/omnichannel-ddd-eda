<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Support;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;

final class ClientIncidentReportTenantResolver
{
    public function resolveInstanceTenant(): ?TenantModel
    {
        $slug = Str::slug((string) config('platform.client_slug', ''));

        if ($slug !== '') {
            $bySlug = TenantModel::query()->where('slug', $slug)->first();
            if ($bySlug !== null) {
                return $bySlug;
            }
        }

        return TenantModel::query()->orderBy('created_at')->first();
    }
}
