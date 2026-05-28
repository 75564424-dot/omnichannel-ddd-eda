<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Module;
use App\Middleware\Domain\ModuleRegistry;
use App\Middleware\Infrastructure\Persistence\Models\MiddlewareRegisteredModule;

final class DatabaseModuleRegistry implements ModuleRegistry
{
    public function recordProducerObservation(string $logicalId, string $name, string $eventType): void
    {
        $this->mergeObservation($logicalId, Module::TYPE_PRODUCER, $name, $eventType);
    }

    public function recordConsumerObservation(string $logicalId, string $name, string $eventType): void
    {
        $this->mergeObservation($logicalId, Module::TYPE_CONSUMER, $name, $eventType);
    }

    public function listModules(?string $typeFilter = null): array
    {
        $q = MiddlewareRegisteredModule::query()->orderBy('logical_id');

        if ($typeFilter !== null) {
            $q->where('type', $typeFilter);
        }

        $out = [];
        foreach ($q->get() as $row) {
            $out[] = Module::fromPersistence([
                'logical_id'  => $row->logical_id,
                'name'        => $row->name,
                'type'        => $row->type,
                'event_types' => $row->event_types ?? [],
            ]);
        }

        return $out;
    }

    private function mergeObservation(string $logicalId, string $type, string $name, string $eventType): void
    {
        if ($logicalId === '' || $eventType === '') {
            return;
        }

        $row = MiddlewareRegisteredModule::query()
            ->where('logical_id', $logicalId)
            ->where('type', $type)
            ->first();

        $types = $row !== null ? ($row->event_types ?? []) : [];
        $types[] = $eventType;
        $types = array_values(array_unique($types));

        if ($row === null) {
            MiddlewareRegisteredModule::query()->create([
                'logical_id'  => $logicalId,
                'type'        => $type,
                'name'        => $name !== '' ? $name : $logicalId,
                'event_types' => $types,
            ]);
            return;
        }

        $row->name = $name !== '' ? $name : $row->name;
        $row->event_types = $types;
        $row->save();
    }
}
