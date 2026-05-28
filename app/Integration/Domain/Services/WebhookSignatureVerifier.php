<?php

declare(strict_types=1);

namespace App\Integration\Domain\Services;

/**
 * Verifies HMAC-SHA256 webhook signatures (Plan_Integraciones).
 */
final class WebhookSignatureVerifier
{
    public function verify(string $rawBody, string $secret, ?string $signatureHeader): bool
    {
        if ($signatureHeader === null || $signatureHeader === '' || $secret === '') {
            return false;
        }

        $provided = $signatureHeader;
        if (str_starts_with(strtolower($provided), 'sha256=')) {
            $provided = substr($provided, 7);
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $provided);
    }
}
