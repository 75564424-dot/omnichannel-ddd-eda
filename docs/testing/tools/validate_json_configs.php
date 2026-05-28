<?php

declare(strict_types=1);

/**
 * Valida JSON de configuración declarativa del middleware (CI gate).
 * Uso: php docs/testing/tools/validate_json_configs.php
 */
$root = dirname(__DIR__, 3);

$files = [
    'modules_config' => $root.'/config/modules/modules_config.json',
    'dashboard_config' => $root.'/config/dashboard_config.json',
];

$errors = [];

foreach ($files as $label => $path) {
    if (! is_readable($path)) {
        $errors[] = "{$label}: file not readable at {$path}";

        continue;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        $errors[] = "{$label}: unable to read {$path}";

        continue;
    }

    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        $errors[] = "{$label}: invalid JSON — {$e->getMessage()}";

        continue;
    }

    if (! is_array($decoded)) {
        $errors[] = "{$label}: root must be a JSON object";

        continue;
    }

    if ($label === 'modules_config') {
        $errors = array_merge($errors, validateModulesConfig($decoded, $path));
    }

    if ($label === 'dashboard_config') {
        $errors = array_merge($errors, validateDashboardConfig($decoded, $path));
    }
}

if ($errors !== []) {
    fwrite(STDERR, "JSON config validation failed:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, "  - {$error}\n");
    }

    exit(1);
}

fwrite(STDOUT, 'JSON config validation passed ('.count($files)." files).\n");

exit(0);

/**
 * @param  array<string, mixed>  $config
 * @return list<string>
 */
function validateModulesConfig(array $config, string $path): array
{
    $errors = [];

    if (! isset($config['middleware']) || ! is_array($config['middleware'])) {
        $errors[] = "modules_config ({$path}): missing or invalid \"middleware\" object";
    }

    foreach (['producers', 'subscribers'] as $key) {
        if (! isset($config[$key])) {
            continue;
        }
        if (! is_array($config[$key])) {
            $errors[] = "modules_config ({$path}): \"{$key}\" must be an array";

            continue;
        }
        foreach (array_values($config[$key]) as $index => $row) {
            if (! is_array($row)) {
                $errors[] = "modules_config ({$path}): {$key}[{$index}] must be an object";

                continue;
            }
            if (trim((string) ($row['id'] ?? '')) === '') {
                $errors[] = "modules_config ({$path}): {$key}[{$index}] missing \"id\"";
            }
            if (trim((string) ($row['name'] ?? '')) === '') {
                $errors[] = "modules_config ({$path}): {$key}[{$index}] missing \"name\"";
            }
            $typesKey = $key === 'producers' ? 'event_types_emitted' : 'event_types_consumed';
            if (isset($row[$typesKey]) && ! is_array($row[$typesKey])) {
                $errors[] = "modules_config ({$path}): {$key}[{$index}].{$typesKey} must be an array";
            }
        }
    }

    return $errors;
}

/**
 * @param  array<string, mixed>  $config
 * @return list<string>
 */
function validateDashboardConfig(array $config, string $path): array
{
    $errors = [];

    foreach (['counter_cards', 'metrics'] as $key) {
        if (! isset($config[$key])) {
            continue;
        }
        if (! is_array($config[$key])) {
            $errors[] = "dashboard_config ({$path}): \"{$key}\" must be an array";
        }
    }

    return $errors;
}
