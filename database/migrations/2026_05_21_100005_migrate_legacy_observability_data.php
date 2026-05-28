<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrates data from legacy observability tables to the new middleware schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->migrateMessageQueue();
        $this->migrateDeadLetterQueue();
        $this->migrateEventFeedProjections();
        $this->migrateObservabilityMetrics();
        $this->migrateChannelStatusSnapshots();
        $this->migrateRegisteredModules();
        $this->seedDefaultChannelAndNode();
    }

    public function down(): void
    {
        // Data migration is not reversible without legacy tables.
    }

    private function migrateMessageQueue(): void
    {
        if (! Schema::hasTable('bus_queue_entries')) {
            return;
        }

        $statusMap = [
            'PENDING'   => 'pending',
            'PROCESADO' => 'completed',
            'FALLIDO'   => 'failed',
        ];

        DB::table('bus_queue_entries')->orderBy('id')->chunk(500, function ($rows) use ($statusMap) {
            foreach ($rows as $row) {
                $exists = DB::table('message_queue')->where('event_uuid', $row->event_id)->exists();
                if ($exists) {
                    continue;
                }

                DB::table('message_queue')->insert([
                    'event_uuid'           => $row->event_id,
                    'tenant_id'            => null,
                    'message_type'         => $row->event_type,
                    'origin'               => $row->origin ?? 'Unknown',
                    'channel_id'           => null,
                    'integration_id'       => null,
                    'target_consumers'     => $row->consumers,
                    'payload'              => $row->payload,
                    'status'               => $statusMap[$row->status] ?? strtolower($row->status),
                    'priority'             => 0,
                    'correlation_id'       => null,
                    'published_at'         => $row->published_at,
                    'dispatched_at'        => $row->dispatched_at,
                    'processing_time_ms'   => $row->processing_time_ms,
                    'attempt_count'        => $row->attempt_count ?? 0,
                    'max_attempts'         => 3,
                    'created_at'           => $row->created_at ?? now(),
                    'updated_at'           => $row->updated_at ?? now(),
                ]);
            }
        });
    }

    private function migrateDeadLetterQueue(): void
    {
        if (! Schema::hasTable('bus_dead_letters')) {
            return;
        }

        DB::table('bus_dead_letters')->orderBy('id')->chunk(500, function ($rows) {
            foreach ($rows as $row) {
                $exists = DB::table('dead_letter_queue')->where('event_uuid', $row->event_id)->exists();
                if ($exists) {
                    continue;
                }

                $messageQueueId = DB::table('message_queue')
                    ->where('event_uuid', $row->event_id)
                    ->value('id');

                DB::table('dead_letter_queue')->insert([
                    'message_queue_id'   => $messageQueueId,
                    'event_uuid'         => $row->event_id,
                    'event_type'         => $row->event_type,
                    'origin'             => $row->origin ?? 'Unknown',
                    'payload'            => $row->payload,
                    'failure_reason'     => $row->failure_reason,
                    'failure_code'       => null,
                    'retry_count'        => 0,
                    'failed_at'          => $row->failed_at,
                    'resolved_at'        => $row->resolved_at,
                    'resolution_action'  => $row->resolved_at ? 'manual' : null,
                    'created_at'         => $row->failed_at ?? now(),
                ]);
            }
        });
    }

    private function migrateEventFeedProjections(): void
    {
        if (! Schema::hasTable('event_feed_entries')) {
            return;
        }

        DB::table('event_feed_entries')->orderBy('id')->chunk(500, function ($rows) {
            foreach ($rows as $row) {
                $eventUuid = $this->normalizeUuid($row->event_id);
                $exists = DB::table('event_feed_projections')->where('event_uuid', $eventUuid)->exists();
                if ($exists) {
                    continue;
                }

                DB::table('event_feed_projections')->insert([
                    'event_uuid'   => $eventUuid,
                    'event_type'   => $row->event_type,
                    'origin'       => $row->origin,
                    'impact'       => $row->impact,
                    'status'       => $row->status,
                    'occurred_at'  => $row->occurred_at,
                    'received_at'  => $row->received_at,
                    'raw_payload'  => $row->raw_payload,
                    'created_at'   => $row->created_at ?? now(),
                ]);
            }
        });
    }

    private function migrateObservabilityMetrics(): void
    {
        if (Schema::hasTable('bus_metrics_snapshots')) {
            DB::table('bus_metrics_snapshots')->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $recordedAt = $row->recorded_at;
                    $this->insertMetric('bus', 'latency_ms', $row->latency_ms, $recordedAt);
                    $this->insertMetric('bus', 'events_per_second', $row->events_per_second, $recordedAt);
                    $this->insertMetric('bus', 'error_rate', $row->error_rate, $recordedAt);
                    $this->insertMetric('bus', 'dead_letters_count', $row->dead_letters_count, $recordedAt);
                    $this->insertMetric('bus', 'stream_status', $this->statusToNumeric($row->bus_status), $recordedAt, [
                        'bus_status' => $row->bus_status,
                    ]);
                }
            });
        }

        if (Schema::hasTable('middleware_bus_metrics')) {
            DB::table('middleware_bus_metrics')->orderBy('id')->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $recordedAt = $row->recorded_at;
                    $this->insertMetric('bus', 'latency_ms', $row->latency_ms, $recordedAt, ['source' => 'dashboard']);
                    $this->insertMetric('bus', 'events_per_second', $row->processing_rate_eps, $recordedAt, ['source' => 'dashboard']);
                    $this->insertMetric('bus', 'queue_size', $row->queue_size, $recordedAt, ['source' => 'dashboard']);
                    $this->insertMetric('bus', 'stream_status', $this->statusToNumeric($row->stream_status), $recordedAt, [
                        'stream_status' => $row->stream_status,
                        'source'        => 'dashboard',
                    ]);
                }
            });
        }
    }

    private function migrateChannelStatusSnapshots(): void
    {
        if (! Schema::hasTable('node_status_snapshots')) {
            return;
        }

        DB::table('node_status_snapshots')->orderBy('id')->get()->each(function ($row) {
            $exists = DB::table('channel_status_snapshots')->where('node_code', $row->node_name)->exists();
            if ($exists) {
                return;
            }

            DB::table('channel_status_snapshots')->insert([
                'channel_id'      => null,
                'node_code'       => $row->node_name,
                'status'          => $row->status,
                'events_enabled'  => $row->middleware_events_enabled ?? true,
                'metadata'        => null,
                'recorded_at'     => $row->updated_at ?? now(),
                'updated_at'      => $row->updated_at ?? now(),
            ]);
        });
    }

    private function migrateRegisteredModules(): void
    {
        if (! Schema::hasTable('middleware_registered_modules')) {
            return;
        }

        DB::table('middleware_registered_modules')->orderBy('id')->get()->each(function ($row) {
            $exists = DB::table('registered_modules')
                ->where('logical_id', $row->logical_id)
                ->where('type', $row->type)
                ->exists();

            if ($exists) {
                return;
            }

            DB::table('registered_modules')->insert([
                'logical_id'  => $row->logical_id,
                'type'        => $row->type,
                'name'        => $row->name,
                'event_types' => $row->event_types,
                'created_at'  => $row->created_at ?? now(),
                'updated_at'  => $row->updated_at ?? now(),
            ]);
        });
    }

    private function seedDefaultChannelAndNode(): void
    {
        if (! DB::table('channel_status_snapshots')->where('node_code', 'middleware')->exists()) {
            DB::table('channel_status_snapshots')->insert([
                'channel_id'     => null,
                'node_code'      => 'middleware',
                'status'         => 'ONLINE',
                'events_enabled' => false,
                'metadata'       => null,
                'recorded_at'    => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    private function insertMetric(string $scope, string $key, mixed $value, mixed $recordedAt, ?array $dimensions = null): void
    {
        DB::table('observability_metrics')->insert([
            'tenant_id'     => null,
            'metric_scope'  => $scope,
            'metric_key'    => $key,
            'metric_value'  => $value,
            'dimensions'    => $dimensions !== null ? json_encode($dimensions) : null,
            'recorded_at'   => $recordedAt,
        ]);
    }

    private function statusToNumeric(string $status): int
    {
        return match (strtoupper($status)) {
            'ACTIVE'   => 1,
            'DEGRADED' => 2,
            'HI-LOAD'  => 3,
            default    => 0,
        };
    }

    private function normalizeUuid(string $eventId): string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $eventId)) {
            return strtolower($eventId);
        }

        return $eventId;
    }
};
