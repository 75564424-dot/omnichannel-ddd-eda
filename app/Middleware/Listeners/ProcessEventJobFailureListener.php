<?php

declare(strict_types=1);

namespace App\Middleware\Listeners;

use App\Middleware\Application\Services\Processing\EventDeadLetterFinalizer;
use App\Middleware\Infrastructure\Jobs\ProcessEventJob;
use Illuminate\Queue\Events\JobFailed;

final class ProcessEventJobFailureListener
{
    public function __construct(
        private readonly EventDeadLetterFinalizer $finalizer,
    ) {}

    public function handle(JobFailed $event): void
    {
        $job = $this->resolveProcessEventJob($event);
        if ($job === null) {
            return;
        }

        $this->finalizer->finalize(
            $job->eventId,
            $job->eventType,
            $job->payload,
            $job->origin,
            $event->exception,
        );
    }

    private function resolveProcessEventJob(JobFailed $event): ?ProcessEventJob
    {
        if (! str_contains($event->job->resolveName(), ProcessEventJob::class)) {
            return null;
        }

        $payload = $event->job->payload();
        $command = $payload['data']['command'] ?? null;
        if (! is_string($command)) {
            return null;
        }

        $instance = unserialize($command, ['allowed_classes' => true]);

        return $instance instanceof ProcessEventJob ? $instance : null;
    }
}
