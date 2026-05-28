<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Domain\ValueObjects\RetryPolicy;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RetryPolicyTest extends TestCase
{
    #[Test]
    public function from_config_reads_max_attempts_and_backoff(): void
    {
        config()->set('eventbus.retry', [
            'max_attempts' => 4,
            'backoff'      => [1, 2, 3],
        ]);

        $policy = RetryPolicy::fromConfig();

        $this->assertSame(4, $policy->maxAttempts);
        $this->assertSame([1, 2, 3], $policy->backoffSeconds);
        $this->assertSame(2, $policy->backoffForAttempt(2));
        $this->assertSame(3, $policy->backoffForAttempt(99));
    }
}
