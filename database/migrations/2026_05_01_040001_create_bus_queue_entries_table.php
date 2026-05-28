<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks every event that passes through the Event Bus.
 * Powers the EventQueueTable and metrics computation.
 * Enforce retention policy to prevent unbounded growth (see config/eventbus.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_queue_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique()->comment('UUID of the domain event');
            $table->string('event_type', 100)->index()->comment('Producer-defined catalog name');
            $table->string('origin', 100)->default('Unknown')->comment('Producer module or gateway');
            $table->json('consumers')->nullable()->comment('Array of consumer module names');
            $table->json('payload')->nullable()->comment('Full event payload — reference only, not source of truth');
            $table->string('status', 20)->default('PENDING')
                ->comment('PENDING | PROCESADO | FALLIDO');
            $table->timestamp('published_at')->nullable()->index()
                ->comment('When the event entered the bus');
            $table->timestamp('dispatched_at')->nullable()
                ->comment('When the event was delivered to all consumers');
            $table->unsignedInteger('processing_time_ms')->nullable()
                ->comment('Latency in ms: dispatched_at - published_at');
            $table->unsignedTinyInteger('attempt_count')->default(0)
                ->comment('Number of dispatch attempts');
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_queue_entries');
    }
};
