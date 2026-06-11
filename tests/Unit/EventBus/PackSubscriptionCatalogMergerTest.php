<?php

declare(strict_types=1);

namespace Tests\Unit\EventBus;

use App\Platform\Demo\DemoPackEventConsumers;
use App\Platform\Demo\DemoPackListener;
use App\Shared\EventBus\PackSubscriptionCatalogMerger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\EventBus\Fixtures\InvalidReturnRegistrar;
use Tests\Unit\EventBus\Fixtures\ThrowingRegistrar;
use Tests\Unit\EventBus\Fixtures\BadInterfaceRegistrar;

final class PackSubscriptionCatalogMergerTest extends TestCase
{
    #[Test]
    public function merges_rows_and_deduplicates_module_listener_pairs(): void
    {
        $merger = new PackSubscriptionCatalogMerger(new \Psr\Log\NullLogger());
        $base = [
            'E1' => [
                ['module' => 'M1'],
                ['module' => 'M2', 'listener' => DemoPackListener::class],
            ],
        ];

        [$subs, $listeners] = $merger->merge(
            [DuplicateFriendlyRegistrar::class],
            $base
        );

        self::assertArrayHasKey('E1', $subs);
        self::assertCount(2, $subs['E1']);
        self::assertArrayHasKey('E2', $subs);
        self::assertCount(1, $subs['E2']);

        $keys = array_map(
            static fn (array $l) => $l['event_type']."\0".$l['listener'],
            $listeners
        );
        self::assertContains("E2\0".DemoPackListener::class, $keys);
    }

    #[Test]
    public function skips_duplicate_from_second_registrar(): void
    {
        $merger = new PackSubscriptionCatalogMerger(new \Psr\Log\NullLogger());
        [$subs] = $merger->merge(
            [DemoPackEventConsumers::class, DemoPackEventConsumers::class],
            []
        );

        self::assertCount(1, $subs['Platform.Demo.Pack']);
    }

    #[Test]
    public function skips_missing_class_and_bad_interface(): void
    {
        $merger = new PackSubscriptionCatalogMerger(new \Psr\Log\NullLogger());
        [$subs] = $merger->merge(
            ['\\Nonexistent\\Class', BadInterfaceRegistrar::class],
            ['Only' => [['module' => 'X']]]
        );

        self::assertSame('X', $subs['Only'][0]['module']);
    }

    #[Test]
    public function skips_throw_and_malformed_catalog_without_wiping_base(): void
    {
        $merger = new PackSubscriptionCatalogMerger(new \Psr\Log\NullLogger());
        [$subs] = $merger->merge(
            [ThrowingRegistrar::class, InvalidReturnRegistrar::class],
            ['K' => [['module' => 'Keep']]]
        );

        self::assertSame(['K' => [['module' => 'Keep']]], $subs);
    }
}

/** @internal */
final class DuplicateFriendlyRegistrar implements \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface
{
    public static function subscriptionCatalog(): array
    {
        return [
            'E1' => [
                ['module' => 'M1'],
                ['module' => 'M2', 'listener' => DemoPackListener::class],
            ],
            'E2' => [
                ['module' => 'M3', 'listener' => DemoPackListener::class],
            ],
        ];
    }
}
