<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\Registrars\EventBusPackSubscriptionBootstrapper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EventBusPackSubscriptionBootstrapperTest extends TestCase
{
    #[Test]
    public function bootstrap_noops_when_no_consumer_registrars(): void
    {
        config(['eventbus.consumer_registrars' => []]);
        $base = config('eventbus.subscriptions');

        (new EventBusPackSubscriptionBootstrapper())->bootstrap();

        $this->assertSame($base, config('eventbus.subscriptions'));
    }
}
