<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts/decrypts integration credentials at rest (Plan_Integraciones).
 */
final class IntegrationCredentialCipher
{
    public function encrypt(string $plain): string
    {
        return Crypt::encryptString($plain);
    }

    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }
}
