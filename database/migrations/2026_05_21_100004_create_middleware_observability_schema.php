<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware persistence — Observability & Audit domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->string('actor_type', 30)->nullable();
            $table->string('actor_id', 100)->nullable();
            $table->string('action', 60);
            $table->string('entity_type', 60);
            $table->string('entity_id', 100);
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'occurred_at']);
        });

        Schema::create('trace_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trace_id');
            $table->uuid('span_id');
            $table->uuid('parent_span_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('event_uuid')->nullable();
            $table->string('operation_name', 120);
            $table->string('service_name', 60);
            $table->string('status', 20);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['trace_id', 'span_id']);
            $table->index('correlation_id');
            $table->index('event_uuid');
        });

        Schema::create('observability_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->string('metric_scope', 30);
            $table->string('metric_key', 60);
            $table->decimal('metric_value', 15, 4);
            $table->json('dimensions')->nullable();
            $table->timestamp('recorded_at');

            $table->index(['metric_scope', 'metric_key', 'recorded_at']);
        });

        Schema::create('channel_status_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('channel_id')->nullable();
            $table->string('node_code', 60)->unique();
            $table->string('status', 20);
            $table->boolean('events_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
        });

        Schema::create('event_feed_projections', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->string('event_type', 120);
            $table->string('origin', 120);
            $table->string('impact', 80);
            $table->string('status', 20)->default('SUCCESS');
            $table->timestamp('occurred_at');
            $table->timestamp('received_at');
            $table->json('raw_payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'occurred_at']);
        });

        Schema::create('registered_modules', function (Blueprint $table) {
            $table->id();
            $table->string('logical_id', 120);
            $table->string('type', 16);
            $table->string('name', 120);
            $table->json('event_types');
            $table->timestamps();

            $table->unique(['logical_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registered_modules');
        Schema::dropIfExists('event_feed_projections');
        Schema::dropIfExists('channel_status_snapshots');
        Schema::dropIfExists('observability_metrics');
        Schema::dropIfExists('trace_logs');
        Schema::dropIfExists('audit_logs');
    }
};
