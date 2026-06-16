<?php

declare(strict_types=1);

namespace App\Shared\Platform\Support;

final class ModulesConfigPath
{
    public static function resolve(): string
    {
        $path = env('MODULES_CONFIG_PATH');

        if (! is_string($path) || trim($path) === '') {
            return config_path('modules/modules_config.json');
        }

        $path = trim($path);

        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:[/\\\\]#', $path) !== 0) {
            return $path;
        }

        return base_path($path);
    }
}
