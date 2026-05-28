<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard Read Store — middleware bus metrics snapshots.
 * Inserted by MiddlewareMetricsListener on every event cycle.
 * Query: take the latest record by `recorded_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('middleware_bus_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('latency_ms')->default(0)->comment('Average processing latency in milliseconds');
            $table->unsignedInteger('processing_rate_eps')->default(0)->comment('Events per second in the last 60 seconds');
            $table->unsignedInteger('queue_size')->default(0)->comment('Estimated FIFO queue depth');
            $table->string('stream_status', 20)->default('STOPPED')->comment('ACTIVE | DEGRADED | STOPPED');
            $table->timestamp('recorded_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('middleware_bus_metrics');
    }
};
