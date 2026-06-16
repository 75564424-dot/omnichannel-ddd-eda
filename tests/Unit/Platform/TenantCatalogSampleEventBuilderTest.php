<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Services\TenantCatalogSampleEventBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantCatalogSampleEventBuilderTest extends TestCase
{
    #[Test]
    public function builds_templates_from_producer_event_types(): void
    {
        $builder = new TenantCatalogSampleEventBuilder;

        $templates = $builder->fromCatalog([
            'producers' => [
                [
                    'id'   => 'pos01',
                    'name' => 'terminal 01',
                    'event_types_emitted' => ['prueba01.sale.complete', 'prueba01.sale.voiced'],
                    'channels' => ['POS'],
                ],
            ],
            'subscribers' => [],
        ]);

        $this->assertCount(2, $templates);
        $this->assertSame('prueba01.sale.complete', $templates[0]['event_type']);
        $this->assertSame('terminal 01', $templates[0]['origin']);
    }
}
