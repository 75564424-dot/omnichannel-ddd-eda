<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes to support retention purge queries and time-range scans (Plan_BaseDeDatos Fase 2).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_queue') && ! $this->hasIndex('message_queue', 'message_queue_published_at_index')) {
            Schema::table('message_queue', function (Blueprint $table) {
                $table->index('published_at', 'message_queue_published_at_index');
            });
        }

        if (Schema::hasTable('event_logs') && ! $this->hasIndex('event_logs', 'event_logs_logged_at_index')) {
            Schema::table('event_logs', function (Blueprint $table) {
                $table->index('logged_at', 'event_logs_logged_at_index');
            });
        }

        if (Schema::hasTable('observability_metrics') && ! $this->hasIndex('observability_metrics', 'observability_metrics_recorded_at_index')) {
            Schema::table('observability_metrics', function (Blueprint $table) {
                $table->index('recorded_at', 'observability_metrics_recorded_at_index');
            });
        }

        if (Schema::hasTable('event_store') && ! $this->hasIndex('event_store', 'event_store_occurred_at_index')) {
            Schema::table('event_store', function (Blueprint $table) {
                $table->index('occurred_at', 'event_store_occurred_at_index');
            });
        }

        if (Schema::hasTable('audit_logs') && ! $this->hasIndex('audit_logs', 'audit_logs_occurred_at_index')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('occurred_at', 'audit_logs_occurred_at_index');
            });
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('message_queue', 'message_queue_published_at_index');
        $this->dropIndexIfExists('event_logs', 'event_logs_logged_at_index');
        $this->dropIndexIfExists('observability_metrics', 'observability_metrics_recorded_at_index');
        $this->dropIndexIfExists('event_store', 'event_store_occurred_at_index');
        $this->dropIndexIfExists('audit_logs', 'audit_logs_occurred_at_index');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $indexes = Schema::getConnection()->select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $index) {
                if (($index->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $result   = Schema::getConnection()->select(
            'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return ((int) ($result[0]->c ?? 0)) > 0;
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
};
