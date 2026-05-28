<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard Read Store — event feed projections.
 *
 * `event_id`   UNIQUE: idempotency key — prevents duplicate projections.
 * `raw_payload` JSON: full event payload stored for traceability.
 * `latency_ms` computed on PHP side but readable via (received_at - occurred_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_feed_entries', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100)->unique()->comment('Idempotency key — unique per event');
            $table->string('event_type', 60)->index()->comment('Event name from envelope');
            $table->string('origin', 60)->comment('Observed origin label (channel, gateway, or explicit hint)');
            $table->string('impact', 80)->comment('Human-readable impact: -3 SKU, +50 Units, +1 Pedido, SYNC +10u');
            $table->string('status', 20)->default('SUCCESS')->comment('SUCCESS | PENDING | FAILED');
            $table->timestamp('occurred_at')->index()->comment('Timestamp when the domain event occurred');
            $table->timestamp('received_at')->useCurrent()->comment('Timestamp when the listener received the event');
            $table->json('raw_payload')->comment('Full original event payload');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_feed_entries');
    }
};
