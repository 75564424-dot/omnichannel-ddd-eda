<?php

declare(strict_types=1);

namespace App\Console\Commands\Demo;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;

final class EmitMockPlatformEventCommand extends Command
{
    protected $signature = 'platform:emit-mock
                            {--type=PlatformPing : String event name dispatched on the app event bus}';

    protected $description = 'Dispatches a mock envelope on the app event bus for dashboard/middleware smoke tests';

    public function handle(Dispatcher $events): int
    {
        $type = (string) $this->option('type');
        $id = Uuid::uuid4()->toString();

        $events->dispatch($type, [[
            'event_id' => $id,
            'event' => $type,
            'event_type' => $type,
            'channel' => 'MOCK_CLI',
            'occurred_at' => now()->toIso8601String(),
            'message' => 'platform:emit-mock',
        ]]);

        $this->info("Dispatched [{$type}] event_id={$id}");

        return self::SUCCESS;
    }
}
