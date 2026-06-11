<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use Illuminate\Contracts\Encryption\Encrypter;

/**
 * Encrypts/decrypts integration credentials at rest (Plan_Integraciones).
 */
final class IntegrationCredentialCipher
{
    public function __construct(
        private readonly Encrypter $encrypter,
    ) {}

    public function encrypt(string $plain): string
    {
        return $this->encrypter->encryptString($plain);
    }

    public function decrypt(string $encrypted): string
    {
        return $this->encrypter->decryptString($encrypted);
    }
}
