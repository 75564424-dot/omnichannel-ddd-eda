<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Application\Services\ModuleActivationGateService;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModuleActivationGateServiceTest extends TestCase
{
    #[Test]
    public function simulation_blocked_when_middleware_inactive(): void
    {
        $repo = $this->createMock(NodeStatusRepositoryInterface::class);
        $repo->method('middlewareEventsEnabled')->willReturnMap([
            ['middleware', false],
            ['producer:pos_a', true],
        ]);

        $gate = new ModuleActivationGateService($repo);
        $reason = $gate->simulationBlockReason([
            'producers' => [
                ['id' => 'pos_a', 'name' => 'POS A', 'event_types_emitted' => ['Order.Created']],
            ],
        ]);

        $this->assertSame(
            'Active el bus de eventos (Middleware) en el panel Live antes de simular.',
            $reason,
        );
    }

    #[Test]
    public function simulation_blocked_when_all_producers_inactive(): void
    {
        $repo = $this->createMock(NodeStatusRepositoryInterface::class);
        $repo->method('middlewareEventsEnabled')->willReturnMap([
            ['middleware', true],
            ['producer:pos_a', false],
        ]);

        $gate = new ModuleActivationGateService($repo);
        $reason = $gate->simulationBlockReason([
            'producers' => [
                ['id' => 'pos_a', 'name' => 'POS A', 'event_types_emitted' => ['Order.Created']],
            ],
        ]);

        $this->assertStringContainsString('Active los productores configurados', (string) $reason);
        $this->assertStringContainsString('POS A', (string) $reason);
    }
}
