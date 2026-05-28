<?php

declare(strict_types=1);

namespace Tests\Unit\Platform\LocalFleet;

use App\Shared\Platform\LocalFleet\LocalFleetRegistry;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LocalFleetRegistryTest extends TestCase
{
    #[Test]
    public function it_allocates_next_port_and_upserts_by_slug(): void
    {
        $path = storage_path('framework/testing/fleet-registry-'.Str::uuid().'.json');
        $registry = new LocalFleetRegistry($path, 8001);

        $first = $registry->upsert([
            'label' => 'Acme',
            'slug'  => 'acme-retail',
        ]);

        $second = $registry->upsert([
            'label' => 'Beta',
            'slug'  => 'beta-retail',
        ]);

        $this->assertSame(8001, $first['port']);
        $this->assertSame(8002, $second['port']);
        $this->assertNotNull($registry->findBySlug('acme-retail'));

        $updated = $registry->upsert([
            'slug'  => 'acme-retail',
            'label' => 'Acme Updated',
            'port'  => 8001,
        ]);

        $this->assertSame('Acme Updated', $updated['label']);
        $this->assertCount(2, $registry->clientInstances());

        @unlink($path);
    }
}
