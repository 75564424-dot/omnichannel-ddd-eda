<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Hooks;

/**
 * Integration packs may register hooks to react to a new row in the event feed read-model
 * (e.g. KPI counters) without coupling the core dashboard to a specific domain.
 */
interface AfterEventFeedInsertHookInterface
{
    public function onNewFeedRow(string $eventType, array $payload): void;
}
