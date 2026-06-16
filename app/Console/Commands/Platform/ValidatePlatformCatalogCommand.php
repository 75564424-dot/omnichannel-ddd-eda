<?php

declare(strict_types=1);

namespace App\Console\Commands\Platform;

use App\Shared\Platform\Services\PlatformCatalogValidator;
use Illuminate\Console\Command;

final class ValidatePlatformCatalogCommand extends Command
{
    protected $signature = 'platform:validate-catalog';

    protected $description = 'Validates alignment between modules_config.json and eventbus routing (B.3 / CI gate)';

    public function handle(PlatformCatalogValidator $validator): int
    {
        $errors = $validator->validate();

        if ($errors === []) {
            $this->info('Platform catalog validation passed.');

            return self::SUCCESS;
        }

        $this->error('Platform catalog validation failed:');
        foreach ($errors as $error) {
            $this->line("  - {$error}");
        }

        return self::FAILURE;
    }
}
