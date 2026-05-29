<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Middleware\Application\Services\SimulationPublishScope;
use App\Middleware\Application\Services\SimulationPulseService;
use App\Middleware\Application\Services\SimulationQueueDrainer;
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
                    $this->simulationDrainer->drain([$eventId]);
                    if ($onProgress !== null) {
                        $onProgress($current, $total);
                    }
                },
            );
        } finally {
            $this->simulationScope->endDeferring();
            $this->simulationPulse->clear();
        }

        if ($result['validation_errors'] !== []) {
            throw new RuntimeException('Validación de catálogo: '.implode('; ', $result['validation_errors']));
        }

        return [
            'published'         => $result['published'],
            'queue_matches'     => $result['queue_matches'],
            'event_ids'         => $result['event_ids'],
            'validation_errors' => [],
        ];
    }
}
