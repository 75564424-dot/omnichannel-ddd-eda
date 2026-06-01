<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet\Contracts;

use App\Shared\Infrastructure\Models\TenantModel;

interface LocalFleetTenantMirrorInterface
{
    public function mirror(TenantModel $controlPlaneTenant): void;
}

