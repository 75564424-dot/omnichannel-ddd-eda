<?php

declare(strict_types=1);

namespace App\Quality\Interfaces\Providers;

use Illuminate\Support\ServiceProvider;

final class QualityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 4).'/config/platform_quality.php',
            'platform_quality',
        );
    }
}
