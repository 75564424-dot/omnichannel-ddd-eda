<?php

declare(strict_types=1);

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class PurgePlatformRetentionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function purge_retention_deletes_old_message_queue_rows(): void
    {
        $this->seed(\Database\Seeders\InstanceTenantSeeder::class);
        $this->seed(\Database\Seeders\MiddlewareDatabaseSeeder::class);

        $oldId = Uuid::uuid4()->toString();
        $newId = Uuid::uuid4()->toString();

        DB::table('message_queue')->insert([
            [
                'event_uuid'   => $oldId,
                'message_type' => 'Test.Old',
                'origin'       => 'Test',
                'status'       => 'completed',
                'published_at' => now()->subDays(60),
                'created_at'   => now()->subDays(60),
                'updated_at'   => now()->subDays(60),
            ],
            [
                'event_uuid'   => $newId,
                'message_type' => 'Test.New',
                'origin'       => 'Test',
                'status'       => 'completed',
                'published_at' => now()->subDay(),
                'created_at'   => now()->subDay(),
                'updated_at'   => now()->subDay(),
            ],
        ]);

        $this->artisan('platform:purge-retention', ['--table' => 'message_queue'])
            ->assertSuccessful();

        $this->assertDatabaseMissing('message_queue', ['event_uuid' => $oldId]);
        $this->assertDatabaseHas('message_queue', ['event_uuid' => $newId]);
    }

    #[Test]
    public function purge_retention_dry_run_does_not_delete(): void
    {
        $eventId = Uuid::uuid4()->toString();

        DB::table('message_queue')->insert([
            'event_uuid'   => $eventId,
            'message_type' => 'Test.Old',
            'origin'       => 'Test',
            'status'       => 'processed',
            'published_at' => now()->subDays(60),
            'created_at'   => now()->subDays(60),
            'updated_at'   => now()->subDays(60),
        ]);

        $this->artisan('platform:purge-retention', [
            '--dry-run' => true,
            '--table'   => 'message_queue',
        ])->assertSuccessful();

        $this->assertDatabaseHas('message_queue', ['event_uuid' => $eventId]);
    }

    #[Test]
    public function middleware_database_seeder_creates_default_channel(): void
    {
        $this->seed(\Database\Seeders\InstanceTenantSeeder::class);
        $this->seed(\Database\Seeders\MiddlewareDatabaseSeeder::class);

        $this->assertDatabaseHas('channels', ['code' => 'middleware']);
        $this->assertDatabaseHas('system_configurations', [
            'config_key' => 'retention.message_queue_days',
        ]);
    }
}
