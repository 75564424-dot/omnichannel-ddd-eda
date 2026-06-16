<?php

declare(strict_types=1);

namespace Tests\Unit\Quality;

use App\Quality\Application\Services\Coverage\ApplicationCoverageCalculator;
use App\Quality\Application\Services\Coverage\ApplicationCoverageGateService;
use App\Quality\Application\Services\QualitySettings;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApplicationCoverageGateServiceTest extends TestCase
{
    #[Test]
    public function evaluate_passes_when_coverage_meets_threshold(): void
    {
        $gate = new ApplicationCoverageGateService(
            new QualitySettings(['coverage' => ['application_min_percent' => 70]]),
            new ApplicationCoverageCalculator(),
        );

        $result = $gate->evaluate(base_path('tests/Fixtures/quality/sample-clover.xml'));

        $this->assertTrue($result->passed);
        $this->assertSame(80.0, $result->percent);
    }

    #[Test]
    public function evaluate_fails_when_coverage_below_threshold(): void
    {
        $gate = new ApplicationCoverageGateService(
            new QualitySettings(['coverage' => ['application_min_percent' => 90]]),
            new ApplicationCoverageCalculator(),
        );

        $result = $gate->evaluate(base_path('tests/Fixtures/quality/sample-clover.xml'));

        $this->assertFalse($result->passed);
    }
}
