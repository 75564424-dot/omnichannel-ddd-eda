<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

/**
 * Retry policy for event processing (Plan_Resiliencia).
 */
final readonly class RetryPolicy
{
    /**
     * @param list<int> $backoffSeconds
     */
    public function __construct(
        public int $maxAttempts,
        public array $backoffSeconds,
    ) {}

    public static function fromConfig(): self
    {
        /** @var array<string, mixed> $retry */
        $retry = config('eventbus.retry', []);

        $maxAttempts = max(1, (int) ($retry['max_attempts'] ?? 3));
        $backoff     = $retry['backoff'] ?? [5, 30, 120];
        if (! is_array($backoff)) {
            $backoff = [5, 30, 120];
        }

        return new self(
            maxAttempts: $maxAttempts,
            backoffSeconds: array_values(array_map(static fn ($s) => max(0, (int) $s), $backoff)),
        );
    }

    public function backoffForAttempt(int $attempt): int
    {
        if ($this->backoffSeconds === []) {
            return 0;
        }

        $index = max(0, min($attempt - 1, count($this->backoffSeconds) - 1));

        return $this->backoffSeconds[$index];
    }
}
