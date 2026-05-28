<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Entities\DeadLetterEntry;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Infrastructure\Models\DeadLetterModel;
use App\Middleware\Infrastructure\Models\QueueEntryModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EloquentDeadLetterRepository implements DeadLetterRepositoryInterface
{
    public function save(DeadLetterEntry $entry): void
    {
        $messageQueueId = QueueEntryModel::where('event_uuid', $entry->eventId())->value('id');

        DeadLetterModel::updateOrCreate(
            ['event_uuid' => $entry->eventId()],
            [
                'message_queue_id' => $messageQueueId,
                'event_type'       => $entry->eventType(),
                'origin'           => $entry->origin(),
                'payload'          => $entry->payload(),
                'failure_reason'   => $entry->failureReason(),
                'failed_at'        => $entry->failedAt()->format('Y-m-d H:i:s'),
                'resolved_at'      => $entry->resolvedAt()?->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function existsByEventId(string $eventId): bool
    {
        return DeadLetterModel::where('event_uuid', $eventId)->exists();
    }

    public function findUnresolved(): array
    {
        return DeadLetterModel::whereNull('resolved_at')
            ->orderByDesc('failed_at')
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->all();
    }

    public function countUnresolved(): int
    {
        return DeadLetterModel::whereNull('resolved_at')->count();
    }

    public function markResolved(int $id): void
    {
        DeadLetterModel::where('id', $id)->update([
            'resolved_at'       => now()->toDateTimeString(),
            'resolution_action' => 'manual',
        ]);
    }

    public function findById(int $id): ?DeadLetterEntry
    {
        $model = DeadLetterModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function markRequeued(int $id): void
    {
        DeadLetterModel::where('id', $id)->update([
            'resolved_at'       => now()->toDateTimeString(),
            'resolution_action' => 'requeue',
        ]);
    }

    /**
     * Reads from Laravel's failed_jobs table and inserts into dead_letter_queue.
     */
    public function syncFromFailedJobs(): int
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(100)
            ->get();

        $synced = 0;

        foreach ($failedJobs as $job) {
            try {
                $jobPayload = json_decode($job->payload, true) ?? [];
                $jobData    = json_decode($jobPayload['data']['command'] ?? '{}', true);

                $eventId   = $jobData['eventId']   ?? $jobData['event_id']   ?? $job->uuid;
                $eventType = $jobData['eventType']  ?? $jobData['event_type'] ?? $job->queue;
                $origin    = $jobData['origin']     ?? 'Unknown';
                $payload   = $jobData['payload']    ?? [];

                if ($this->existsByEventId($eventId)) {
                    continue;
                }

                $messageQueueId = QueueEntryModel::where('event_uuid', $eventId)->value('id');

                DeadLetterModel::create([
                    'message_queue_id' => $messageQueueId,
                    'event_uuid'       => $eventId,
                    'event_type'       => $eventType,
                    'origin'           => $origin,
                    'payload'          => $payload,
                    'failure_reason'   => $job->exception,
                    'failed_at'        => $job->failed_at,
                    'resolved_at'      => null,
                ]);
                $synced++;
            } catch (\Throwable $e) {
                Log::warning('[EventBus] Dead letter sync failed for job', [
                    'job_uuid' => $job->uuid ?? 'unknown',
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        return $synced;
    }

    private function toDomain(DeadLetterModel $model): DeadLetterEntry
    {
        return DeadLetterEntry::reconstitute(
            id:            $model->id,
            eventId:       $model->event_uuid,
            eventType:     $model->event_type,
            origin:        $model->origin,
            payload:       $model->payload ?? [],
            failureReason: $model->failure_reason,
            failedAt:      DateTimeImmutable::createFromInterface($model->failed_at),
            resolvedAt:    $model->resolved_at
                             ? DateTimeImmutable::createFromInterface($model->resolved_at)
                             : null,
        );
    }
}
