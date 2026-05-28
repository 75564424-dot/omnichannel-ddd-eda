<?php

declare(strict_types=1);

namespace App\Middleware\Domain;

/**
 * Read-model of a module as inferred from bus traffic (not configuration).
 */
final class Module
{
    public const TYPE_PRODUCER = 'producer';
    public const TYPE_CONSUMER = 'consumer';

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $type,
        public readonly array $subscribedEvents,
        public readonly array $publishedEvents,
    ) {
    }

    /**
     * @param array{logical_id: string, name: string, type: string, event_types: array<int, string>} $row
     */
    public static function fromPersistence(array $row): self
    {
        $type = $row['type'];
        $events = array_values(array_unique(array_filter($row['event_types'] ?? [])));

        return new self(
            id: $row['logical_id'],
            name: $row['name'],
            type: $type,
            subscribedEvents: $type === self::TYPE_CONSUMER ? $events : [],
            publishedEvents: $type === self::TYPE_PRODUCER ? $events : [],
        );
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     type: string,
     *     subscribed_events: list<string>,
     *     published_events: list<string>,
     * }
     */
    public function toDetailArray(): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'type'               => $this->type,
            'subscribed_events'  => $this->subscribedEvents,
            'published_events'   => $this->publishedEvents,
        ];
    }
}
