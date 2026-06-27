<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Support\PlatformDatabaseReadiness;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PlatformDatabaseReadinessTest extends TestCase
{
    #[Test]
    public function can_query_schema_is_false_when_sqlite_file_missing(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => base_path('database/does-not-exist.sqlite'),
        ]);

        $this->assertFalse(PlatformDatabaseReadiness::canQuerySchema());
    }

    #[Test]
    public function can_query_schema_is_true_for_in_memory_sqlite(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        $this->assertTrue(PlatformDatabaseReadiness::canQuerySchema());
    }

    #[Test]
    public function can_query_schema_is_true_when_sqlite_file_exists(): void
    {
        $path = base_path('database/instances/.gitkeep');
        $this->assertFileExists($path);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $path,
        ]);

        $this->assertTrue(PlatformDatabaseReadiness::canQuerySchema());
    }
}
