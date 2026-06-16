<?php

declare(strict_types=1);

namespace App\Control\Application\Services\Support;

final class ClientIncidentReportSeverityNormalizer
{
    public function normalize(string $severity): string
    {
        $severity = strtolower($severity);

        return in_array($severity, ['low', 'normal', 'high', 'critical'], true) ? $severity : 'normal';
    }
}
