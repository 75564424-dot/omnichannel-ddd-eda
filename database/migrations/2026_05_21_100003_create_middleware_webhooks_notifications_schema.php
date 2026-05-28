<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware persistence — Webhooks & Notifications domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('integration_id')->nullable();
            $table->uuid('channel_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->string('http_method', 10);
            $table->string('request_path', 500);
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->timestamp('received_at');
            $table->string('status', 20)->default('received');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('integration_id')->references('id')->on('integrations')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
            $table->index(['integration_id', 'received_at']);
            $table->index('correlation_id');
            $table->index('status');
        });

        Schema::create('webhook_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('webhook_request_id');
            $table->unsignedSmallInteger('http_status');
            $table->json('response_headers')->nullable();
            $table->json('response_body')->nullable();
            $table->timestamp('sent_at');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('webhook_request_id')->references('id')->on('webhook_requests')->cascadeOnDelete();
            $table->index('webhook_request_id');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('notification_type', 60);
            $table->string('channel', 30);
            $table->string('recipient', 255);
            $table->string('subject', 255)->nullable();
            $table->json('content');
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('webhook_responses');
        Schema::dropIfExists('webhook_requests');
    }
};
