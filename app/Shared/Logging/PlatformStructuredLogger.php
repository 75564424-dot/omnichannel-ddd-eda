<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use Psr\Log\LoggerInterface;

/**
 * Structured logger with correlation context and PII-safe payload hashing (Plan_Logs).
 */
final class PlatformStructuredLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $this->enrich($context));
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $this->enrich($context));
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $this->enrich($context));
    }

    /** @param array<string, mixed> $context */
    private function enrich(array $context): array
    {
        return array_merge(StructuredLogContext::toArray(), $this->sanitize($context));
    }

    /** @param array<string, mixed> $context */
    private function sanitize(array $context): array
    {
        /** @var list<string> $redact */
        $redact = config('platform_logging.redact_keys', []);

        foreach ($context as $key => $value) {
            if ($this->shouldRedact((string) $key, $redact)) {
                unset($context[$key]);
                continue;
            }

            if ($key === 'payload' && is_array($value)) {
                $context['payload_hash'] = hash('sha256', json_encode($value, JSON_THROW_ON_ERROR));
                unset($context['payload']);
            }
        }

        return $context;
    }

    /** @param list<string> $redact */
    private function shouldRedact(string $key, array $redact): bool
    {
        foreach ($redact as $needle) {
            if (str_contains(strtolower($key), strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
