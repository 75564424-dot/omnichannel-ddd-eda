<?php

declare(strict_types=1);

namespace App\Monitoring\Domain\ValueObjects;

/**
 * Alert severity levels (Plan_Monitoreo).
 */
enum AlertSeverity: string
{
    case P1 = 'P1';
    case P2 = 'P2';
    case P3 = 'P3';
}
