<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Platform;

use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\TenantCatalogEventBusMapper;
use App\Shared\Platform\Services\TenantCatalogNormalizer;
use App\Shared\Platform\Services\TenantCatalogRuntimeConfigurator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantCatalogRuntimeConfiguratorTest extends TestCase
{
    #[Test]
    public function apply_merges_catalog_into_modules_and_eventbus_config(): void
    {
        config()->set('eventbus.producers', ['existing' => ['label' => 'Existing', 'produces' => ['Legacy.Event']]]);
        config()->set('eventbus.subscriptions', []);

        (new TenantCatalogRuntimeConfigurator(
            new TenantCatalogNormalizer(),
            new TenantCatalogEventBusMapper(),
        ))->apply([
            'producers' => [[
                'id'                  => 'pos_main',
                'name'                => 'POS Main',
                'event_types_emitted' => ['Retail.Order.Created'],
            ]],
            'subscribers' => [[
                'id'                   => 'analytics',
                'name'                 => 'Analytics',
                'event_types_consumed' => ['Retail.Order.Created'],
            ]],
            'service_contact_message' => 'Contact support',
        ]);

        $catalog = config('modules.catalog');
        $subscriptions = config('eventbus.subscriptions');
        $producers = config('eventbus.producers');
        $this->assertSame('Contact support', config('modules.service_contact_message'));
        $this->assertSame('pos_main', $catalog['producers'][0]['id']);
        $this->assertSame('Retail.Order.Created', $producers['pos_main']['produces'][0]);
        $this->assertSame('analytics', $subscriptions['Retail.Order.Created'][0]['module']);
        $this->assertSame('Legacy.Event', $producers['existing']['produces'][0]);
    }

    #[Test]
    public function normalizer_skips_invalid_producer_rows(): void
    {
        $normalized = (new TenantCatalogNormalizer())->normalize([
            'producers' => [
                ['id' => '', 'event_types_emitted' => ['Skip.Me']],
                ['id' => 'valid', 'name' => 'Valid', 'event_types_emitted' => ['Ok.Event']],
            ],
            'subscribers' => [],
        ]);

        $mapped = (new TenantCatalogEventBusMapper())->map($normalized);

        $this->assertSame(['valid' => ['label' => 'Valid', 'produces' => ['Ok.Event']]], $mapped['producers']);
    }
}
