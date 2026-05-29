<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use App\Integration\Domain\Services\WebhookSignatureVerifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebhookSignatureVerifierTest extends TestCase
{
    #[Test]
    public function verify_accepts_valid_hmac_sha256(): void
    {
        $body = '{"event_type":"Ping"}';
        $secret = 'test-secret';
        $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

        $this->assertTrue((new WebhookSignatureVerifier())->verify($body, $secret, $signature));
    }

    #[Test]
    public function verify_rejects_invalid_signature(): void
    {
        $this->assertFalse((new WebhookSignatureVerifier())->verify('{}', 'secret', 'bad-sig'));
    }

    #[Test]
    public function verify_rejects_missing_signature(): void
    {
        $this->assertFalse((new WebhookSignatureVerifier())->verify('{}', 'secret', null));
    }
}
