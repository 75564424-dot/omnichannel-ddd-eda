<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('started_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('fixture_slug', 40);
            $table->unsignedSmallInteger('events_per_minute');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('planned_total');
            $table->boolean('prepare_first')->default(true);
            $table->unsignedInteger('published')->default(0);
            $table->unsignedInteger('queue_matches')->default(0);
            $table->unsignedInteger('progress_current')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metrics')->nullable();
            $table->json('event_ids')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_runs');
    }
};
