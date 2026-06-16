<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\LocalFleet\LocalFleetOrphanPruner;
use Illuminate\Console\Command;

final class PruneLocalFleetClientsCommand extends Command
{
    protected $signature = 'platform:fleet:prune-orphans
                            {--slug=* : Extra slugs to remove (e.g. retail-norte)}';

    protected $description = 'Remove client tenants/silos not listed in fleet-registry.json';

    public function handle(LocalFleetOrphanPruner $pruner): int
    {
        $extra = array_map('strval', $this->option('slug') ?? []);

        foreach ($pruner->prune($extra) as $line) {
            $this->line($line);
        }

        $this->info('Prune complete.');

        return self::SUCCESS;
    }
}
