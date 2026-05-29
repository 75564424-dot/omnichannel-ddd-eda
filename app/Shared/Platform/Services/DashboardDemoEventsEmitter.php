<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;

final class DashboardDemoEventsEmitter
{
    private const EVENT_TYPE = 'Platform.Demo.Measurement';

    /** @return array{dispatched: int, bus_rows: bool} */
    public function emit(int $count, bool $withBusRows): array
    {
        $n = max(0, min(50, $count));
        $channels = ['WEB', 'POS', 'MOBILE', 'PARTNER_API'];

        for ($i = 0; $i < $n; $i++) {
            $id = Uuid::uuid4()->toString();
            $dayOffset = $i % 7;
            Event::dispatch(self::EVENT_TYPE, [[
                'event_id' => $id,
                'event' => self::EVENT_TYPE,
                'event_type' => self::EVENT_TYPE,
                'occurred_at' => now()->subDays($dayOffset)->toIso8601String(),
                'channel' => $channels[$i % count($channels)],
                'measurement' => [
                    'amount' => round(25 + ($i * 17.5), 2),
                ],
            ]]);
        }

        if ($withBusRows) {
            $this->seedBusQueueIllustrations();
        }

        return ['dispatched' => $n, 'bus_rows' => $withBusRows];
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
                'event_uuid' => Uuid::uuid4()->toString(),
                'message_type' => 'DemoSynthetic',
                'origin' => $meta['origin'],
                'target_consumers' => $meta['consumers'],
                'payload' => json_encode(['note' => 'platform:demo-dashboard-events --bus-rows']),
                'status' => 'completed',
                'published_at' => $now->clone()->subHours($i),
                'dispatched_at' => $now->clone()->subHours($i),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
