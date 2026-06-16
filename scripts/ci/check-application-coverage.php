<?php

declare(strict_types=1);

/**
 * CI wrapper — delegates to platform:quality-coverage (Plan_Calidad).
 *
 * Usage: php scripts/ci/check-application-coverage.php [clover.xml] [min-percent]
 */
require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';

/** @var Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$params = [];
if (isset($argv[1]) && $argv[1] !== '') {
    $params['--clover'] = $argv[1];
}
if (isset($argv[2]) && $argv[2] !== '') {
    $params['--min'] = $argv[2];
}

exit($kernel->call('platform:quality-coverage', $params));
