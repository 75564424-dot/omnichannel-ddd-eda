<?php

declare(strict_types=1);

/**
 * PHP 8.5 deprecates PDO::MYSQL_ATTR_SSL_CA; prefer Pdo\Mysql::ATTR_SSL_CA when available.
 * Laravel 11.x vendor defaults still use the legacy constant. Patch locally until framework catches up.
 *
 * Safe on PHP < 8.5 (no-op). Idempotent (skips if already patched).
 */
if (PHP_VERSION_ID < 80500) {
    exit(0);
}

$caKeyExpr = "(defined('Pdo\\Mysql::ATTR_SSL_CA') ? \\Pdo\\Mysql::ATTR_SSL_CA : \\PDO::MYSQL_ATTR_SSL_CA)";

$databaseConfig = dirname(__DIR__).'/vendor/laravel/framework/config/database.php';
if (is_readable($databaseConfig)) {
    $contents = file_get_contents($databaseConfig);
    if ($contents !== false && ! str_contains($contents, "defined('Pdo\\Mysql::ATTR_SSL_CA')")) {
        $updated = str_replace(
            'PDO::MYSQL_ATTR_SSL_CA => env(\'MYSQL_ATTR_SSL_CA\'),',
            $caKeyExpr." => env('MYSQL_ATTR_SSL_CA'),",
            $contents
        );
        if ($updated !== $contents) {
            file_put_contents($databaseConfig, $updated);
        }
    }
}

$schemaState = dirname(__DIR__).'/vendor/laravel/framework/src/Illuminate/Database/Schema/MySqlSchemaState.php';
if (is_readable($schemaState)) {
    $contents = file_get_contents($schemaState);
    if ($contents !== false && ! str_contains($contents, "defined('Pdo\\Mysql::ATTR_SSL_CA')")) {
        $updated = str_replace('\\PDO::MYSQL_ATTR_SSL_CA', $caKeyExpr, $contents);
        if ($updated !== $contents) {
            file_put_contents($schemaState, $updated);
        }
    }
}
