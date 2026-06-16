<?php

declare(strict_types=1);

namespace Tests\Unit\Quality;

use App\Quality\Application\Services\Coverage\ApplicationCoverageCalculator;
use App\Quality\Application\Services\QualitySettings;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApplicationCoverageCalculatorTest extends TestCase
{
    #[Test]
    public function calculate_counts_only_application_layer_prefixes(): void
    {
        $calculator = new ApplicationCoverageCalculator();
        $settings   = new QualitySettings([]);

        $metrics = $calculator->calculate(
            base_path('tests/Fixtures/quality/sample-clover.xml'),
            $settings->applicationCoveragePrefixes(),
        );

        $this->assertSame(5, $metrics['total']);
        $this->assertSame(4, $metrics['covered']);
        $this->assertSame(80.0, $metrics['percent']);
    }
}
