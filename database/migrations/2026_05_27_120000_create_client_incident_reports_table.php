<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_incident_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name', 120);
            $table->string('reporter_email', 190);
            $table->string('tenant_name', 120)->nullable();
            $table->string('tenant_slug', 80)->nullable();
            $table->string('subject', 160)->nullable();
            $table->text('description');
            $table->string('severity', 20)->default('normal');
            $table->string('status', 20)->default('open');
            $table->string('page_url', 500)->nullable();
            $table->json('diagnostic_log');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_incident_reports');
    }
};
