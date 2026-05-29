<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\Registrars\SqliteConcurrencyConfigurator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SqliteConcurrencyConfiguratorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function configure_runs_without_exception_on_sqlite_memory(): void
    {
        config(['database.default' => 'sqlite']);

        (new SqliteConcurrencyConfigurator())->configure();

        $this->assertSame('sqlite', config('database.default'));
    }
}
