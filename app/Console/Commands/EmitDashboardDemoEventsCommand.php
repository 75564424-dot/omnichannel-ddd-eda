<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;

/**
 * Simulated traffic for configurable dashboard metrics (event feed + optional message_queue analytics rows).
 * Does not embed domain rules — only plausible envelopes for integration / UI testing.
 */
final class EmitDashboardDemoEventsCommand extends Command
{
    protected $signature = 'platform:demo-dashboard-events
                            {--bus-rows : Insert illustrative message_queue rows for origin/consumer charts}
                            {--count=5 : Number of Platform.Demo.Measurement samples to dispatch}';

    protected $description = 'Dispatches generic sample events and optionally seeds bus_queue rows for dashboard QA';

    public function handle(): int
    {
        $n = max(0, min(50, (int) $this->option('count')));
        $channels = ['WEB', 'POS', 'MOBILE', 'PARTNER_API'];
        $eventType = 'Platform.Demo.Measurement';

        for ($i = 0; $i < $n; $i++) {
            $id = Uuid::uuid4()->toString();
            $dayOffset = $i % 7;
            Event::dispatch($eventType, [[
                'event_id'    => $id,
                'event'       => $eventType,
                'event_type'  => $eventType,
                'occurred_at' => now()->subDays($dayOffset)->toIso8601String(),
                'channel'     => $channels[$i % count($channels)],
                'measurement' => [
                    'amount' => round(25 + ($i * 17.5), 2),
                ],
            ]]);
        }

        $this->info("Dispatched {$n} {$eventType} event(s).");

        if ($this->option('bus-rows')) {
            $this->seedBusQueueIllustrations();
            $this->info('Inserted synthetic message_queue rows (analytics only).');
        }

        return self::SUCCESS;
    }

    private function seedBusQueueIllustrations(): void
    {
        $now = now();
        $rows = [
            ['origin' => 'POS', 'consumers' => json_encode(['ExternalPackA', 'AnalyticsSink'])],
            ['origin' => 'WEB', 'consumers' => json_encode(['ExternalPackB'])],
            ['origin' => 'PARTNER_API', 'consumers' => json_encode([])],
            ['origin' => 'MOBILE', 'consumers' => json_encode(['ExternalPackA'])],
        ];

        foreach ($rows as $i => $meta) {
            DB::table('message_queue')->insert([
                'event_uuid'        => Uuid::uuid4()->toString(),
                'message_type'      => 'DemoSynthetic',
                'origin'            => $meta['origin'],
                'target_consumers'  => $meta['consumers'],
                'payload'           => json_encode(['note' => 'platform:demo-dashboard-events --bus-rows']),
                'status'            => 'completed',
                'published_at'      => $now->clone()->subHours($i),
                'dispatched_at'     => $now->clone()->subHours($i),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }
}
