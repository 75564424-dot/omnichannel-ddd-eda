<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Middleware\Application\Services\SubscriptionRegistryService;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Middleware routing registry + wildcard bus listeners — config-driven, no cross-BC imports.
 */
final class SubscriptionRegistryAndBusRegistrationIntegrationTest extends TestCase
{
    #[Test]
    public function core_catalog_is_empty_so_external_packs_define_types_and_consumers(): void
    {
        /** @var SubscriptionRegistryService $registry */
        $registry = app(SubscriptionRegistryService::class);

        $this->assertSame([], config('eventbus.subscriptions'));
        $this->assertTrue($registry->getConsumersFor('Any.External.Event')->isEmpty());
        $this->assertFalse($registry->isKnownEventType('Any.External.Event'));
    }

    #[Test]
    public function wildcard_platform_listeners_are_registered(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = app('events');

        $this->assertTrue(
            $dispatcher->hasListeners('*'),
            'Bus tracking must observe arbitrary string events without hard-coded catalog keys.',
        );
    }
}
