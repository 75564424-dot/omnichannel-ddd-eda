<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_feed_projections') && ! Schema::hasColumn('event_feed_projections', 'correlation_id')) {
            Schema::table('event_feed_projections', function (Blueprint $table) {
                $table->uuid('correlation_id')->nullable()->after('event_uuid');
                $table->index('correlation_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('event_feed_projections', 'correlation_id')) {
            Schema::table('event_feed_projections', function (Blueprint $table) {
                $table->dropIndex(['correlation_id']);
                $table->dropColumn('correlation_id');
            });
        }
    }
};
