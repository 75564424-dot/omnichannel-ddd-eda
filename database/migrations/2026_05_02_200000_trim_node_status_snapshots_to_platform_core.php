<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Core platform ships a single monitored node (middleware). Remove demo retail rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('node_status_snapshots')) {
            return;
        }

        DB::table('node_status_snapshots')->whereIn('node_name', [
            'ventas_web',
            'pos',
            'inventario',
            'pedidos',
        ])->delete();

        DB::table('node_status_snapshots')->updateOrInsert(
            ['node_name' => 'middleware'],
            [
                'status'                      => 'OFFLINE',
                'middleware_events_enabled' => true,
                'updated_at'                  => now(),
            ],
        );
    }

    public function down(): void
    {
        // Intentionally one-way; retail seed rows are not restored automatically.
    }
};
