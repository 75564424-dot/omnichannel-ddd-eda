<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

/**
 * User-facing simulation messages (single source of truth).
 */
final class SimulationMessages
{
    public const REPLACED_BY_NEW_RUN =
        'Reemplazada por una nueva simulación (proceso anterior colgado).';

    public const MANUAL_RESET =
        'Marcada como fallida (reset manual / proceso colgado).';

    public const TENANT_NOT_FOUND = 'Tenant no encontrado.';

    public const WORKER_WRONG_HOST =
        'El worker de simulación arrancó en el control plane en lugar del silo cliente '
        .'(revise APP_ENV / PLATFORM_CONTROL_PLANE del subproceso).';
}
