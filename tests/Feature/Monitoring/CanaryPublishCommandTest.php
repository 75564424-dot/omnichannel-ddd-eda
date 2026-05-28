<?php

declare(strict_types=1);

namespace Tests\Feature\Monitoring;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CanaryPublishCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function canary_publish_command_succeeds_on_healthy_bus(): void
    {
        $this->artisan('platform:canary-publish')
            ->assertSuccessful();

        $this->assertDatabaseHas('message_queue', [
            'message_type' => 'Platform.Monitoring.Canary',
        ]);
    }
}
