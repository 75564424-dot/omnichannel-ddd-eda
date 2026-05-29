<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationPulseServiceTest extends TestCase
{
    #[Test]
    public function snapshot_clears_stale_pulse_and_reports_inactive(): void
    {
        Cache::flush();

        $service = app(SimulationPulseService::class);
        Cache::put('middleware.simulation_pulse', [
            'active'   => true,
            'phase'    => 'publish',
            'sequence' => 3,
            'tick_at'  => now()->subMinutes(5)->toIso8601String(),
        ], now()->addHour());

        $snapshot = $service->snapshot();

        $this->assertFalse($snapshot['active']);
        $this->assertNull(Cache::get('middleware.simulation_pulse'));
    }

    #[Test]
    public function snapshot_keeps_recent_pulse_active(): void
    {
        Cache::flush();

        $service = app(SimulationPulseService::class);
        $service->tick('dispatch', 'OrderCreated');

        $snapshot = $service->snapshot();

        $this->assertTrue($snapshot['active']);
        $this->assertSame('dispatch', $snapshot['phase']);
    }
}
