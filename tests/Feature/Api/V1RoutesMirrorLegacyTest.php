<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class V1RoutesMirrorLegacyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function v1_queue_matches_legacy_behavior(): void
    {
        $legacy = $this->getJson('/api/middleware/queue?limit=5');
        $v1     = $this->getJson('/api/v1/middleware/queue?limit=5');

        $legacy->assertOk();
        $v1->assertOk();
        $this->assertSame($legacy->json('success'), $v1->json('success'));
    }

    #[Test]
    public function v1_topology_is_available(): void
    {
        $this->getJson('/api/v1/middleware/topology')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
