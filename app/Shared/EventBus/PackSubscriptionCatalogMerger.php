<?php

declare(strict_types=1);

namespace App\Shared\EventBus;

use App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Merges static registration catalogs from {@see EventConsumerRegistrationInterface}
 * into the normalized eventbus.subscriptions shape (module + optional metadata).
 */
final class PackSubscriptionCatalogMerger
{
    /**
     * @param  iterable<int, class-string>  $registrarClasses
     * @param  array<string, mixed>  $baseSubscriptions  event_type => list of subscriber rows
     * @return array{0: array<string, list<array<string, mixed>>>, 1: list<array{event_type: string, listener: class-string}>}
     */
    public function merge(iterable $registrarClasses, array $baseSubscriptions): array
    {
        $subscriptions = $this->cloneSubscriptions($baseSubscriptions);
        $listenersToRegister = [];
        $listenerKeys = [];

        foreach ($registrarClasses as $class) {
            if (! is_string($class) || $class === '') {
                continue;
            }
            if (! class_exists($class)) {
                Log::warning('[EventBus] Pack registrar class not found — skipped.', ['class' => $class]);

                continue;
            }
            if (! is_subclass_of($class, EventConsumerRegistrationInterface::class)) {
                Log::warning('[EventBus] Pack registrar does not implement EventConsumerRegistrationInterface — skipped.', ['class' => $class]);

                continue;
            }
            try {
                /** @var array<string, mixed> $catalog */
                $catalog = $class::subscriptionCatalog();
            } catch (Throwable $e) {
                Log::warning('[EventBus] Pack subscriptionCatalog() failed — skipped.', [
                    'class'      => $class,
                    'error'      => $e->getMessage(),
                    'exception'  => $e,
                ]);

                continue;
            }
            if (! is_array($catalog)) {
                Log::warning('[EventBus] Pack subscriptionCatalog() must return array — skipped.', ['class' => $class]);

                continue;
            }

            foreach ($catalog as $eventType => $rows) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }
                if (! is_array($rows)) {
                    continue;
                }
                $existing = $subscriptions[$eventType] ?? [];
                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $normalized = $this->normalizeRow($row);
                    if ($normalized['module'] === '') {
                        continue;
                    }
                    if ($this->listContainsEquivalentRow($existing, $normalized)) {
                        $this->maybeQueueListener($normalized, $eventType, $listenersToRegister, $listenerKeys);

                        continue;
                    }
                    $existing[] = $this->rowForSubscription($normalized);
                    $this->maybeQueueListener($normalized, $eventType, $listenersToRegister, $listenerKeys);
                }
                $subscriptions[$eventType] = $existing;
            }
        }

        return [$subscriptions, $listenersToRegister];
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, list<array<string, mixed>>>
     */
    private function cloneSubscriptions(array $base): array
    {
        $out = [];
        foreach ($base as $eventType => $rows) {
            $eventType = trim((string) $eventType);
            if ($eventType === '' || ! is_array($rows)) {
                continue;
            }
            $copy = [];
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $copy[] = $row;
                }
            }
            $out[$eventType] = $copy;
        }

        return $out;
    }

    /**
     * @return array{module: string, listener: string, queue: string}
     */
    private function normalizeRow(array $row): array
    {
        return [
            'module'   => trim((string) ($row['module'] ?? '')),
            'listener' => trim((string) ($row['listener'] ?? '')),
            'queue'    => trim((string) ($row['queue'] ?? '')),
        ];
    }

    /**
     * @param  array{module: string, listener: string, queue: string}  $normalized
     * @return array<string, mixed>
     */
    private function rowForSubscription(array $normalized): array
    {
        $out = ['module' => $normalized['module']];
        if ($normalized['listener'] !== '') {
            $out['listener'] = $normalized['listener'];
        }
        if ($normalized['queue'] !== '') {
            $out['queue'] = $normalized['queue'];
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $existing
     * @param  array{module: string, listener: string, queue: string}  $candidate
     */
    private function listContainsEquivalentRow(array $existing, array $candidate): bool
    {
        foreach ($existing as $row) {
            if (! is_array($row)) {
                continue;
            }
            $m = trim((string) ($row['module'] ?? ''));
            $l = trim((string) ($row['listener'] ?? ''));
            if ($m === $candidate['module'] && $l === $candidate['listener']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{module: string, listener: string, queue: string}  $normalized
     * @param  list<array{event_type: string, listener: class-string}>  $listenersToRegister
     * @param  array<string, true>  $listenerKeys
     */
    private function maybeQueueListener(
        array $normalized,
        string $eventType,
        array &$listenersToRegister,
        array &$listenerKeys,
    ): void {
        $listener = $normalized['listener'];
        if ($listener === '' || ! class_exists($listener)) {
            return;
        }
        $key = $eventType."\0".$listener;
        if (isset($listenerKeys[$key])) {
            return;
        }
        $listenerKeys[$key] = true;
        $listenersToRegister[] = [
            'event_type' => $eventType,
            'listener'   => $listener,
        ];
    }
}
