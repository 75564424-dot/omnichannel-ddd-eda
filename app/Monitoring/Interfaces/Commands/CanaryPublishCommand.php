<?php

declare(strict_types=1);

namespace App\Monitoring\Interfaces\Commands;

use App\Monitoring\Application\Services\CanaryPublishService;
use App\Shared\Logging\PlatformStructuredLogger;
use Illuminate\Console\Command;

final class CanaryPublishCommand extends Command
{
    protected $signature = 'platform:canary-publish';

    protected $description = 'Publishes a synthetic canary event to verify bus health (Plan_Monitoreo Fase 3)';

    public function handle(CanaryPublishService $canary, PlatformStructuredLogger $logger): int
    {
        $ok = $canary->run();

        if ($ok) {
            $logger->info('Monitoring canary publish succeeded');
            $this->info('Canary publish succeeded.');

            return self::SUCCESS;
        }

        $logger->error('Monitoring canary publish failed');
        $this->error('Canary publish failed — bus may be degraded.');

        return self::FAILURE;
    }
}
