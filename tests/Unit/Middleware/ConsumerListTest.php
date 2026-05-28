<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\ConsumerList;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConsumerListTest extends TestCase
{
    #[Test]
    public function trims_and_filters_empty_strings(): void
    {
        $list = new ConsumerList([' A ', '', ' B ', ' B ']);
        $this->assertSame(['A', 'B', 'B'], $list->toArray());
        $this->assertSame(3, $list->count());
    }

    #[Test]
    public function empty_factory_has_no_consumers(): void
    {
        $this->assertTrue(ConsumerList::empty()->isEmpty());
    }

    #[Test]
    public function contains_detects_module(): void
    {
        $list = ConsumerList::of('Inventario', 'Dashboard');
        $this->assertTrue($list->contains('Dashboard'));
        $this->assertFalse($list->contains('Pedidos'));
    }
}
