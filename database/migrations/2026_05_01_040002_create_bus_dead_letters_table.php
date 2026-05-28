<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dead-letter queue: events that exhausted all retries.
 * Populated by syncing from Laravel's failed_jobs table.
 * Requires manual intervention: resolve or re-enqueue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_dead_letters', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100)->unique()->comment('UUID of the failed event');
            $table->string('event_type', 100)->index()->comment('Type of the failed event');
            $table->string('origin', 100)->default('Unknown');
            $table->json('payload')->nullable()->comment('Event payload at time of failure');
            $table->text('failure_reason')->comment('Exception message from failed job');
            $table->timestamp('failed_at')->comment('When the last retry was exhausted');
            $table->timestamp('resolved_at')->nullable()
                ->comment('When an operator dismissed or re-enqueued this entry');

            $table->index('failed_at');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_dead_letters');
    }
};
