<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Services\PlatformCatalogValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ValidatePlatformCatalogTest extends TestCase
{
    #[Test]
    public function passes_when_both_catalogs_are_empty(): void
    {
        config()->set('eventbus.producers', []);
        config()->set('eventbus.subscriptions', []);
        config()->set('modules.catalog', [
            'middleware' => ['id' => 'middleware'],
            'producers' => [],
            'subscribers' => [],
        ]);

        $errors = (new PlatformCatalogValidator)->validate();

        $this->assertSame([], $errors);
    }

    #[Test]
    public function fails_when_producer_declared_in_json_but_missing_in_eventbus(): void
    {
        config()->set('eventbus.producers', []);
        config()->set('eventbus.subscriptions', []);
        config()->set('modules.catalog', [
            'producers' => [
                [
                    'id' => 'retail_api',
                    'name' => 'Retail API',
                    'event_types_emitted' => ['Order.Placed'],
                ],
            ],
            'subscribers' => [],
        ]);

        $errors = (new PlatformCatalogValidator)->validate();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('retail_api', implode(' ', $errors));
    }

    #[Test]
    public function fails_when_subscriber_declared_in_json_but_not_subscribed_in_eventbus(): void
    {
        config()->set('eventbus.producers', [
            'retail_api' => ['label' => 'Retail', 'produces' => ['Order.Placed']],
        ]);
        config()->set('eventbus.subscriptions', []);
        config()->set('modules.catalog', [
            'producers' => [
                [
                    'id' => 'retail_api',
                    'name' => 'Retail API',
                    'event_types_emitted' => ['Order.Placed'],
                ],
            ],
            'subscribers' => [
                [
                    'id' => 'inventory',
                    'name' => 'Inventory',
                    'event_types_consumed' => ['Order.Placed'],
                ],
            ],
        ]);

        $errors = (new PlatformCatalogValidator)->validate();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('inventory', implode(' ', $errors));
    }

    #[Test]
    public function passes_when_declarative_and_eventbus_are_aligned(): void
    {
        config()->set('eventbus.producers', [
            'retail_api' => ['label' => 'Retail', 'produces' => ['Order.Placed']],
        ]);
        config()->set('eventbus.subscriptions', [
            'Order.Placed' => [
                ['module' => 'inventory'],
            ],
        ]);
        config()->set('modules.catalog', [
            'producers' => [
                [
                    'id' => 'retail_api',
                    'name' => 'Retail API',
                    'event_types_emitted' => ['Order.Placed'],
                ],
            ],
            'subscribers' => [
                [
                    'id' => 'inventory',
                    'name' => 'Inventory',
                    'event_types_consumed' => ['Order.Placed'],
                ],
            ],
        ]);

        $errors = (new PlatformCatalogValidator)->validate();

        $this->assertSame([], $errors);
    }
}
