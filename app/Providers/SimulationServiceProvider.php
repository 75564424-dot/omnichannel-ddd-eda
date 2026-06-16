<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Registrars\SimulationServiceBindingsRegistrar;
use Illuminate\Support\ServiceProvider;

final class SimulationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        SimulationServiceBindingsRegistrar::register($this->app);
    }
}
