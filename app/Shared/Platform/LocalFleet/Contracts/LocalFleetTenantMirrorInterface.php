<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet\Contracts;

use App\Shared\Infrastructure\Models\TenantModel;

interface LocalFleetTenantMirrorInterface
{
    public function mirror(TenantModel $controlPlaneTenant): void;

    /** Propagate modules_catalog + modules_config.json only (no operator sync). */
    public function mirrorCatalog(TenantModel $controlPlaneTenant): void;
}

