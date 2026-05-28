<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_incident_reports', function (Blueprint $table): void {
            $table->text('admin_response')->nullable()->after('diagnostic_log');
            $table->string('responded_by_name', 120)->nullable()->after('admin_response');
            $table->timestamp('responded_at')->nullable()->after('responded_by_name');
            $table->timestamp('client_read_at')->nullable()->after('responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('client_incident_reports', function (Blueprint $table): void {
            $table->dropColumn(['admin_response', 'responded_by_name', 'responded_at', 'client_read_at']);
        });
    }
};
