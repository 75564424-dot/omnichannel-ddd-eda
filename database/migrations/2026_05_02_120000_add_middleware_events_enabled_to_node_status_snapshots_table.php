<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('node_status_snapshots', function (Blueprint $table) {
            $table->boolean('middleware_events_enabled')
                ->default(true)
                ->after('status')
                ->comment('If false, middleware does not ingest events from this node');
        });
    }

    public function down(): void
    {
        Schema::table('node_status_snapshots', function (Blueprint $table) {
            $table->dropColumn('middleware_events_enabled');
        });
    }
};
