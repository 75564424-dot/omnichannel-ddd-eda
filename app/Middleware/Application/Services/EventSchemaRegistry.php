<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves JSON Schema definitions per event_type from config files or system_configurations.
 */
final class EventSchemaRegistry
{
    /**
     * @return array{path: string, event_version: int, schema_version: string|null}|null
     */
    public function resolve(string $eventType): ?array
    {
        $fromDb = $this->resolveFromDatabase($eventType);
        if ($fromDb !== null) {
            return $fromDb;
        }

        /** @var array<string, mixed> $registry */
        $registry = config('eventbus.schema_registry', []);
        if (! isset($registry[$eventType]) || ! is_array($registry[$eventType])) {
            return $this->resolveLegacyPublishSchema($eventType);
        }

        $entry = $registry[$eventType];
        $path  = $entry['path'] ?? null;
        if (! is_string($path) || ! is_readable($path)) {
            return null;
        }

        return [
            'path'           => $path,
            'event_version'  => (int) ($entry['event_version'] ?? 1),
            'schema_version' => isset($entry['schema_version']) ? (string) $entry['schema_version'] : null,
        ];
    }

    /**
     * @return array{path: string, event_version: int, schema_version: string|null}|null
     */
    private function resolveFromDatabase(string $eventType): ?array
    {
        if (! Schema::hasTable('system_configurations')) {
            return null;
        }

        $key = 'event_schema.'.$eventType;
        $row = DB::table('system_configurations')
            ->where('config_key', $key)
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first();

        if ($row === null) {
            return null;
        }

        $value = json_decode((string) $row->config_value, true);
        if (! is_array($value)) {
            return null;
        }

        $path = $value['path'] ?? null;
        if (! is_string($path) || ! is_readable($path)) {
            return null;
        }

        return [
            'path'           => $path,
            'event_version'  => (int) ($value['event_version'] ?? $row->version ?? 1),
            'schema_version' => isset($value['schema_version']) ? (string) $value['schema_version'] : null,
        ];
    }

    /**
     * @return array{path: string, event_version: int, schema_version: string|null}|null
     */
    private function resolveLegacyPublishSchema(string $eventType): ?array
    {
        /** @var array<string, string> $schemas */
        $schemas = config('eventbus.publish_schemas', []);
        if (! isset($schemas[$eventType])) {
            return null;
        }

        $path = $schemas[$eventType];
        if (! is_readable($path)) {
            return null;
        }

        return [
            'path'           => $path,
            'event_version'  => 1,
            'schema_version' => null,
        ];
    }
}
