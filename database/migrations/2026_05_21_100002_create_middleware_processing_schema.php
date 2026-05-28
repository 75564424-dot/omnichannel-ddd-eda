<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware persistence — Processing & Orchestration domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('job_type', 60);
            $table->string('reference_type', 60)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->json('payload');
            $table->string('status', 20);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['job_type', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('code', 60);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->string('trigger_event_type', 120)->nullable();
            $table->string('status', 20)->default('draft');
            $table->json('config')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
            $table->index(['trigger_event_type', 'status']);
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_id');
            $table->integer('step_order');
            $table->string('step_type', 30);
            $table->string('name', 120);
            $table->json('config')->nullable();
            $table->string('on_failure', 20)->default('retry');
            $table->unsignedInteger('timeout_seconds')->nullable();
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();
            $table->unique(['workflow_id', 'step_order']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('correlation_id')->unique();
            $table->string('transaction_type', 60);
            $table->string('status', 20);
            $table->uuid('channel_id')->nullable();
            $table->uuid('integration_id')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
            $table->foreign('integration_id')->references('id')->on('integrations')->nullOnDelete();
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
        Schema::dropIfExists('processing_jobs');
    }
};
