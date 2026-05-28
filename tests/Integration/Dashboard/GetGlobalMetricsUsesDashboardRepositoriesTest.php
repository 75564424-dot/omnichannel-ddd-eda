<?php

declare(strict_types=1);

namespace Tests\Integration\Dashboard;

use App\Dashboard\Application\UseCases\GetGlobalMetricsUseCase;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * KPI cards resolve from configurable aggregations over feed + bus read models only.
 */
final class GetGlobalMetricsUsesDashboardRepositoriesTest extends TestCase
{
    #[Test]
    public function constructor_uses_only_dashboard_read_repositories(): void
    {
        $method = new ReflectionMethod(GetGlobalMetricsUseCase::class, '__construct');
        $names  = [];
        foreach ($method->getParameters() as $p) {
            $t = $p->getType();
            if ($t instanceof ReflectionNamedType) {
                $names[] = $t->getName();
            }
        }

        $this->assertEqualsCanonicalizing(
            [EventFeedRepositoryInterface::class, BusQueueAnalyticsRepositoryInterface::class],
            $names,
        );
    }
}
