<?php

declare(strict_types=1);

namespace App\Console\Commands\Demo;

use App\Shared\Platform\Services\DashboardDemoEventsEmitter;
use Illuminate\Console\Command;

final class EmitDashboardDemoEventsCommand extends Command
{
    protected $signature = 'platform:demo-dashboard-events
                            {--bus-rows : Insert illustrative message_queue rows for origin/consumer charts}
                            {--count=5 : Number of Platform.Demo.Measurement samples to dispatch}';

    protected $description = 'Dispatches generic sample events and optionally seeds bus_queue rows for dashboard QA';

    public function handle(DashboardDemoEventsEmitter $emitter): int
    {
        $result = $emitter->emit(
            (int) $this->option('count'),
            (bool) $this->option('bus-rows'),
        );

        $this->info("Dispatched {$result['dispatched']} Platform.Demo.Measurement event(s).");

        if ($result['bus_rows']) {
            $this->info('Inserted synthetic message_queue rows (analytics only).');
        }

        return self::SUCCESS;
    }
}
