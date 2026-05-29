<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Execution;


use App\Simulation\Application\Services\Runtime\SimulationPublishScope;
use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use App\Simulation\Application\Services\Runtime\SimulationQueueDrainer;
use App\Shared\Platform\Services\ClientSimulationService;
use RuntimeException;

/**
 * Publishes tenant-catalog events on the client silo with deferred bus processing.
 */
final class ClientSiloSimulationExecutor
{
    public function __construct(
        private readonly ClientSimulationService $simulation,
        private readonly SimulationPulseService $simulationPulse,
        private readonly SimulationPublishScope $simulationScope,
        private readonly SimulationQueueDrainer $simulationDrainer,
    ) {}

    /**
     * @param list<array<string, mixed>> $templates
     * @param callable(int, int): void|null $onProgress
     *
     * @return array{
     *     published: int,
     *     queue_matches: int,
     *     event_ids: list<string>,
     *     validation_errors: list<string>
     * }
     */
    public function execute(
        string $fixtureSlug,
        array $templates,
        int $eventsPerMinute,
        int $durationMinutes,
        bool $skipSync,
        ?callable $onProgress = null,
    ): array {
        if ($templates === []) {
            throw new RuntimeException('No hay plantillas de eventos para simular.');
        }

        set_time_limit(max(600, $durationMinutes * 120 + 120));
        $this->simulationPulse->tick('simulating');

        $this->simulationScope->beginDeferring();

        try {
            $result = $this->simulation->simulate(
                slug: $fixtureSlug,
                events: 0,
                applyFixture: false,
                skipValidate: false,
                skipSync: $skipSync,
                eventsPerMinute: $eventsPerMinute,
                durationMinutes: $durationMinutes,
                sampleTemplates: $templates,
                onPublished: function (int $current, int $total, string $eventId, string $eventType) use ($onProgress): void {
                    $this->simulationPulse->tick('simulating', $eventType);
                    if ($onProgress !== null) {
                        $onProgress($current, $total);
                    }
                    // Process each event right after publish so the client queue/topology update live.
                    $this->simulationDrainer->drain([$eventId]);
                    $this->simulationPulse->tick('simulating', $eventType);
                },
            );

            if ($result['validation_errors'] !== []) {
                return [
                    'published'         => 0,
                    'queue_matches'     => 0,
                    'event_ids'         => [],
                    'validation_errors' => $result['validation_errors'],
                ];
            }

            return [
                'published'         => $result['published'],
                'queue_matches'     => $this->simulation->countQueueMatchesForEventIds($result['event_ids']),
                'event_ids'         => $result['event_ids'],
                'validation_errors' => [],
            ];
        } finally {
            $this->simulationScope->endDeferring();
        }
    }
}
