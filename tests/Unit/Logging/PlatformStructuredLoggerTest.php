<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Shared\Logging\PlatformStructuredLogger;
use App\Shared\Logging\StructuredLogContext;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

final class PlatformStructuredLoggerTest extends TestCase
{
    #[Test]
    public function logger_hashes_payload_and_redacts_secrets(): void
    {
        StructuredLogContext::setCorrelationId('00000000-0000-4000-8000-000000000001');
        StructuredLogContext::setEventUuid('00000000-0000-4000-8000-000000000002');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Test message',
                $this->callback(function (array $context): bool {
                    return isset($context['payload_hash'])
                        && ! isset($context['payload'])
                        && ! isset($context['password'])
                        && ($context['correlation_id'] ?? '') === '00000000-0000-4000-8000-000000000001';
                }),
            );

        (new PlatformStructuredLogger($logger))->info('Test message', [
            'payload'  => ['sku' => 'ABC'],
            'password' => 'secret',
        ]);
    }
}
