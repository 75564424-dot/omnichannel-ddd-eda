<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores periodic snapshots of computed Event Bus health metrics.
 * Powers BusMetricsCards and BusStatusIndicator in the control UI.
 * Snapshots are computed by BusMetricsService (triggered by API or scheduler).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('latency_ms')->default(0)
                ->comment('Average processing latency in ms for last N events');
            $table->unsignedInteger('events_per_second')->default(0)
                ->comment('Events processed per second in last 60s window');
            $table->decimal('error_rate', 5, 2)->default(0.00)
                ->comment('% of failed events in last 60s window');
            $table->unsignedInteger('dead_letters_count')->default(0)
                ->comment('Number of unresolved dead-letter entries');
            $table->string('bus_status', 20)->default('STOPPED')
                ->comment('ACTIVE | DEGRADED | HI-LOAD | STOPPED');
            $table->timestamp('recorded_at')->index()
                ->comment('When this snapshot was computed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_metrics_snapshots');
    }
};
