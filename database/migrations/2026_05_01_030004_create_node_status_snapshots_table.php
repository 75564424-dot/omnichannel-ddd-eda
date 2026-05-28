<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Read Store — one row per system node.
 * Updated by GlobalMetricsProjector when a listener successfully processes an event.
 *
 * Nodes: ventas_web | pos | inventario | pedidos | middleware
 * Status: ONLINE | SYNCING | HI-LOAD | ERROR | OFFLINE
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_status_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('node_name', 40)->unique()->comment('ventas_web | pos | inventario | pedidos | middleware');
            $table->string('status', 20)->default('OFFLINE')->comment('ONLINE | SYNCING | HI-LOAD | ERROR | OFFLINE');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Seed all nodes as OFFLINE — will be set ONLINE once events arrive
        DB::table('node_status_snapshots')->insert([
            ['node_name' => 'ventas_web',  'status' => 'OFFLINE', 'updated_at' => now()],
            ['node_name' => 'pos',         'status' => 'OFFLINE', 'updated_at' => now()],
            ['node_name' => 'inventario',  'status' => 'OFFLINE', 'updated_at' => now()],
            ['node_name' => 'pedidos',     'status' => 'OFFLINE', 'updated_at' => now()],
            ['node_name' => 'middleware',  'status' => 'OFFLINE', 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('node_status_snapshots');
    }
};
