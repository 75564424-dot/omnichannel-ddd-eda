<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\Topology\TopologyRegistryConfigMapper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TopologyRegistryConfigMapperTest extends TestCase
{
    #[Test]
    public function map_producers_preserves_label_and_event_list(): void
    {
        $rows = (new TopologyRegistryConfigMapper())->mapProducers([
            'retail_pos' => [
                'label'    => 'Retail POS',
                'produces' => ['Platform.Order.Created', 'Platform.Order.Created'],
            ],
        ]);

        $this->assertSame([
            [
                'id'     => 'retail_pos',
                'label'  => 'Retail POS',
                'events' => ['Platform.Order.Created', 'Platform.Order.Created'],
            ],
        ], $rows);
    }

    #[Test]
    public function map_consumers_groups_subscriptions_by_module(): void
    {
        $rows = (new TopologyRegistryConfigMapper())->mapConsumers([
            'Platform.Order.Created' => ['Analytics', 'Billing'],
            'Platform.Order.Paid'    => ['Analytics'],
        ]);

        $this->assertEqualsCanonicalizing([
            [
                'id'            => 'analytics',
                'label'         => 'Analytics',
                'subscribed_to' => ['Platform.Order.Created', 'Platform.Order.Paid'],
            ],
            [
                'id'            => 'billing',
                'label'         => 'Billing',
                'subscribed_to' => ['Platform.Order.Created'],
            ],
        ], $rows);
    }
}
