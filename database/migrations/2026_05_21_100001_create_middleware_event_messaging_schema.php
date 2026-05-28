<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware persistence — Events & Messaging domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_store', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('causation_id')->nullable();
            $table->string('aggregate_type', 60)->nullable();
            $table->string('aggregate_id', 100)->nullable();
            $table->string('event_type', 120);
            $table->unsignedInteger('event_version')->default(1);
            $table->string('origin', 120);
            $table->uuid('channel_id')->nullable();
            $table->uuid('integration_id')->nullable();
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('recorded_at');
            $table->string('schema_version', 20)->nullable();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
            $table->foreign('integration_id')->references('id')->on('integrations')->nullOnDelete();
            $table->index(['event_type', 'occurred_at']);
            $table->index('correlation_id');
            $table->index(['aggregate_type', 'aggregate_id']);
        });

        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid');
            $table->uuid('tenant_id')->nullable();
            $table->string('event_type', 120);
            $table->string('origin', 120);
            $table->uuid('channel_id')->nullable();
            $table->uuid('integration_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->string('status', 20);
            $table->string('summary', 255)->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('logged_at');

            $table->index('event_uuid');
            $table->index(['event_type', 'occurred_at']);
            $table->index(['status', 'logged_at']);
            $table->index('correlation_id');
        });

        Schema::create('message_queue', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->uuid('tenant_id')->nullable();
            $table->string('message_type', 120);
            $table->string('origin', 120)->default('Unknown');
            $table->uuid('channel_id')->nullable();
            $table->uuid('integration_id')->nullable();
            $table->json('target_consumers')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('priority')->default(0);
            $table->uuid('correlation_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->unsignedInteger('processing_time_ms')->nullable();
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('message_type');
            $table->index('correlation_id');
        });

        Schema::create('dead_letter_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_queue_id')->nullable();
            $table->uuid('event_uuid')->unique();
            $table->string('event_type', 120);
            $table->string('origin', 120)->default('Unknown');
            $table->json('payload')->nullable();
            $table->text('failure_reason');
            $table->string('failure_code', 60)->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('failed_at');
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution_action', 30)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_type');
            $table->index('failed_at');
            $table->index('resolved_at');
        });

        Schema::create('retries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('message_queue_id');
            $table->uuid('event_uuid');
            $table->unsignedTinyInteger('attempt_number');
            $table->timestamp('scheduled_at');
            $table->timestamp('executed_at')->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['message_queue_id', 'attempt_number']);
            $table->index('event_uuid');
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retries');
        Schema::dropIfExists('dead_letter_queue');
        Schema::dropIfExists('message_queue');
        Schema::dropIfExists('event_logs');
        Schema::dropIfExists('event_store');
    }
};
