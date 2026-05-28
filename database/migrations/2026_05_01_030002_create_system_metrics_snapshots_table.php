<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Read Store — global metrics counters.
 * One row per metric key; updated incrementally by GlobalMetricsProjector.
 *
 * Keys: stock_total | ventas_recientes | ordenes_activas | skus_criticos
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('snapshot_key', 60)->unique()->comment('stock_total | ventas_recientes | ordenes_activas | skus_criticos');
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Seed initial rows so queries never return null
        DB::table('system_metrics_snapshots')->insert([
            ['snapshot_key' => 'stock_total',      'value' => 0, 'updated_at' => now()],
            ['snapshot_key' => 'ventas_recientes',  'value' => 0, 'updated_at' => now()],
            ['snapshot_key' => 'ordenes_activas',   'value' => 0, 'updated_at' => now()],
            ['snapshot_key' => 'skus_criticos',     'value' => 0, 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_metrics_snapshots');
    }
};
