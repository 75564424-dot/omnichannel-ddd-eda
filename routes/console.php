<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('platform:purge-retention')->dailyAt('02:30');

Schedule::command('platform:monitoring-evaluate')
    ->everyMinute()
    ->when(fn () => config('platform_monitoring.enabled', true));

Schedule::command('platform:canary-publish')
    ->everyFiveMinutes()
    ->when(fn () => config('platform_monitoring.canary.enabled', true));
