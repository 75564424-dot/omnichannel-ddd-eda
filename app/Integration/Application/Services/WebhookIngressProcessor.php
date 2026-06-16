<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use App\Integration\Domain\Contracts\ExternalEventPublisherInterface;

final class WebhookIngressProcessor
{
    public function __construct(
        private readonly AdapterPipeline $adapterPipeline,
        private readonly WebhookEventEnvelopeBuilder $envelopeBuilder,
        private readonly ExternalEventPublisherInterface $eventPublisher,
    ) {}

    /**
     * @param array<string, mixed> $integration
     * @param array<string, mixed> $body
     *
     * @return array{event_id: string, entry_id: int}
     */
    public function process(array $integration, array $body): array
    {
        /** @var array<string, mixed> $config */
        $config  = is_array($integration['config']) ? $integration['config'] : [];
        $payload = $this->adapterPipeline->process($integration['id'], $body, $config);
        $envelope = $this->envelopeBuilder->build($integration, $payload, $config);
        $result = $this->eventPublisher->publish($envelope);

        return [
            'event_id' => $envelope['event_id'],
            'entry_id' => $result->entryId,
        ];
    }
}
