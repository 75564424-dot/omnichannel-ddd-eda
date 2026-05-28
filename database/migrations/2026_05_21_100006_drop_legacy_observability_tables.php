<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops legacy observability tables superseded by the middleware schema.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $legacyTables = [
        'bus_queue_entries',
        'bus_dead_letters',
        'event_feed_entries',
        'bus_metrics_snapshots',
        'middleware_bus_metrics',
        'node_status_snapshots',
        'system_metrics_snapshots',
        'middleware_registered_modules',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->legacyTables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Legacy tables are not recreated; run migrate:fresh from original migrations if needed.
    }
};
