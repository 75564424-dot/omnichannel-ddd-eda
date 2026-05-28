<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;

/**
 * Simulates an external producer: publishes a string event + payload array on the app event bus.
 */
final class EmitMockPlatformEventCommand extends Command
{
    protected $signature = 'platform:emit-mock
                            {--type=PlatformPing : String event name dispatched on the app event bus}';

    protected $description = 'Dispatches a mock envelope (Laravel Event facade) for dashboard/middleware smoke tests';

    public function handle(): int
    {
        $type = (string) $this->option('type');
        $id   = Uuid::uuid4()->toString();

        $body = [
            'event_id'    => $id,
            'event'       => $type,
            'event_type'  => $type,
            'channel'     => 'MOCK_CLI',
            'occurred_at' => now()->toIso8601String(),
            'message'     => 'platform:emit-mock',
        ];

        Event::dispatch($type, [$body]);

        $this->info("Dispatched [{$type}] event_id={$id}");

        return self::SUCCESS;
    }
}
