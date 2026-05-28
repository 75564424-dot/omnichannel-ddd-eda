<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Middleware persistence — Configuration & Integration domain.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->string('slug', 80)->unique();
            $table->string('status', 20)->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        Schema::create('system_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('config_key', 120);
            $table->json('config_value');
            $table->string('scope', 30)->default('global');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unique(['tenant_id', 'config_key', 'scope']);
            $table->index('is_active');
        });

        Schema::create('channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('code', 60);
            $table->string('name', 120);
            $table->string('channel_type', 30);
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
            $table->index(['channel_type', 'status']);
        });

        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('code', 60);
            $table->string('name', 120);
            $table->string('provider_type', 30);
            $table->string('base_url', 500)->nullable();
            $table->string('status', 20)->default('active');
            $table->json('capabilities')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
            $table->index('provider_type');
        });

        Schema::create('integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('channel_id')->nullable();
            $table->uuid('provider_id')->nullable();
            $table->string('code', 60);
            $table->string('name', 120);
            $table->string('direction', 20);
            $table->string('status', 20)->default('active');
            $table->json('config')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('channel_id')->references('id')->on('channels')->nullOnDelete();
            $table->foreign('provider_id')->references('id')->on('providers')->nullOnDelete();
            $table->unique(['tenant_id', 'code']);
            $table->index(['status', 'direction']);
        });

        Schema::create('adapters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id');
            $table->string('adapter_type', 30);
            $table->string('handler_class', 255)->nullable();
            $table->json('config')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->foreign('integration_id')->references('id')->on('integrations')->cascadeOnDelete();
            $table->index(['integration_id', 'priority']);
        });

        Schema::create('connectors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id');
            $table->string('connector_type', 30);
            $table->string('endpoint', 500)->nullable();
            $table->json('config')->nullable();
            $table->string('health_status', 20)->default('unknown');
            $table->timestamp('last_health_check_at')->nullable();
            $table->timestamps();

            $table->foreign('integration_id')->references('id')->on('integrations')->cascadeOnDelete();
            $table->index(['integration_id', 'health_status']);
        });

        Schema::create('integration_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('integration_id');
            $table->string('credential_type', 30);
            $table->text('encrypted_value');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();

            $table->foreign('integration_id')->references('id')->on('integrations')->cascadeOnDelete();
            $table->index(['integration_id', 'credential_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_credentials');
        Schema::dropIfExists('connectors');
        Schema::dropIfExists('adapters');
        Schema::dropIfExists('integrations');
        Schema::dropIfExists('providers');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('system_configurations');
        Schema::dropIfExists('tenants');
    }
};
