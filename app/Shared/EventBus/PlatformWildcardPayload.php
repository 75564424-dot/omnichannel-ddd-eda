<?php

declare(strict_types=1);

namespace App\Shared\EventBus;

/**
 * Normalizes Laravel dispatcher arguments for string events vs wildcard ("*") listeners.
 */
final class PlatformWildcardPayload
{
    /**
     * @return array{0: string|null, 1: array<string, mixed>}
     */
    public static function parse(mixed $first, mixed $second = null): array
    {
        if (is_string($first)) {
            $wrapped = is_array($second) ? $second : [];
            $row     = $wrapped[0] ?? null;
            $data    = is_array($row) ? $row : [];

            return [$first, $data];
        }

        $data = is_array($first) ? $first : [];

        return [null, $data];
    }

    public static function shouldObserveWildcardEvent(mixed $eventName): bool
    {
        if (! is_string($eventName)) {
            return true;
        }

        return ! str_starts_with($eventName, 'Illuminate\\')
            && ! str_starts_with($eventName, 'eloquent.');
    }
}
