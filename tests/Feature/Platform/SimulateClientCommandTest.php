<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulateClientCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function simulate_retailco_publishes_events_and_syncs_registry(): void
    {
        $this->artisan('platform:simulate-client', [
            'slug'     => 'retailco',
            '--events' => '4',
        ])->assertSuccessful();

        $this->getJson('/api/dashboard/modules/catalog')
            ->assertOk()
            ->assertJsonPath('producers.0.id', 'retailco_pos');

        $queue = $this->getJson('/api/middleware/queue?limit=20')->assertOk()->json('data');
        $this->assertIsArray($queue);
        $this->assertGreaterThanOrEqual(4, count($queue));
    }

    #[Test]
    public function simulate_acmepos_fixture_is_valid(): void
    {
        $this->artisan('platform:simulate-client', [
            'slug'     => 'acmepos',
            '--events' => '2',
        ])->assertSuccessful();
    }

    #[Test]
    public function simulate_unknown_slug_fails(): void
    {
        $this->artisan('platform:simulate-client', [
            'slug' => 'unknown-client',
        ])->assertFailed();
    }
}
