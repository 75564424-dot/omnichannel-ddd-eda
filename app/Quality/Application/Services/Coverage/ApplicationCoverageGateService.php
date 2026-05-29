<?php

declare(strict_types=1);

namespace App\Quality\Application\Services\Coverage;

use App\Quality\Application\Services\QualitySettings;
use App\Quality\Domain\ValueObjects\CoverageGateResult;

final class ApplicationCoverageGateService
{
    public function __construct(
        private readonly QualitySettings $settings,
        private readonly ApplicationCoverageCalculator $calculator,
    ) {}

    public function evaluate(?string $cloverPath = null, ?float $minPercent = null): CoverageGateResult
    {
        $path = $cloverPath ?? $this->settings->cloverPath();
        $min  = $minPercent ?? (float) $this->settings->coverageMinPercent();

        $metrics = $this->calculator->calculate($path, $this->settings->applicationCoveragePrefixes());

        return new CoverageGateResult(
            percent: round($metrics['percent'], 2),
            coveredStatements: $metrics['covered'],
            totalStatements: $metrics['total'],
            minPercent: $min,
            passed: $metrics['percent'] + 0.0001 >= $min,
        );
    }
}
