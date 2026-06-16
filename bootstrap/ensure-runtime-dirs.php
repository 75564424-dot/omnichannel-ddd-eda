<?php

declare(strict_types=1);

(function (string $root): void {
    foreach ([
        $root.'/storage/app/public',
        $root.'/storage/framework/cache/data',
        $root.'/storage/framework/sessions',
        $root.'/storage/framework/views',
        $root.'/storage/logs',
        $root.'/bootstrap/cache',
    ] as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
})(dirname(__DIR__));
