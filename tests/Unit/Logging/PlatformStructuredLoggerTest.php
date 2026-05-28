<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Shared\Logging\PlatformStructuredLogger;
use App\Shared\Logging\StructuredLogContext;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PlatformStructuredLoggerTest extends TestCase
{
    #[Test]
    public function logger_hashes_payload_and_redacts_secrets(): void
    {
        StructuredLogContext::setCorrelationId('00000000-0000-4000-8000-000000000001');
        StructuredLogContext::setEventUuid('00000000-0000-4000-8000-000000000002');

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Test message'
                    && isset($context['payload_hash'])
                    && ! isset($context['payload'])
                    && ! isset($context['password'])
                    && ($context['correlation_id'] ?? '') === '00000000-0000-4000-8000-000000000001';
            });

        app(PlatformStructuredLogger::class)->info('Test message', [
            'payload'  => ['sku' => 'ABC'],
            'password' => 'secret',
        ]);
    }
}
