<?php

declare(strict_types=1);

namespace App\Quality\Domain\ValueObjects;

final class CoverageGateResult
{
    public function __construct(
        public readonly float $percent,
        public readonly int $coveredStatements,
        public readonly int $totalStatements,
        public readonly float $minPercent,
        public readonly bool $passed,
    ) {}

    /** @return array<string, int|float|bool> */
    public function toArray(): array
    {
        return [
            'percent' => $this->percent,
            'covered_statements' => $this->coveredStatements,
            'total_statements' => $this->totalStatements,
            'min_percent' => $this->minPercent,
            'passed' => $this->passed,
        ];
    }
}
