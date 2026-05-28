<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Application\Services\IntegrationCredentialCipher;
use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentIntegrationCredentialRepository implements IntegrationCredentialRepositoryInterface
{
    public function __construct(
        private readonly IntegrationCredentialCipher $cipher,
    ) {}

    public function store(string $integrationId, string $credentialType, string $plainValue, ?\DateTimeInterface $expiresAt = null): string
    {
        $encrypted = $this->cipher->encrypt($plainValue);
        $existing  = DB::table('integration_credentials')
            ->where('integration_id', $integrationId)
            ->where('credential_type', $credentialType)
            ->first();

        if ($existing !== null) {
            DB::table('integration_credentials')
                ->where('id', $existing->id)
                ->update([
                    'encrypted_value' => $encrypted,
                    'expires_at'      => $expiresAt?->format('Y-m-d H:i:s'),
                    'rotated_at'      => now(),
                    'updated_at'      => now(),
                ]);

            return (string) $existing->id;
        }

        $id = (string) Str::uuid();
        DB::table('integration_credentials')->insert([
            'id'              => $id,
            'integration_id'  => $integrationId,
            'credential_type' => $credentialType,
            'encrypted_value' => $encrypted,
            'expires_at'      => $expiresAt?->format('Y-m-d H:i:s'),
            'rotated_at'      => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return $id;
    }

    public function getPlaintext(string $integrationId, string $credentialType): ?string
    {
        $row = DB::table('integration_credentials')
            ->where('integration_id', $integrationId)
            ->where('credential_type', $credentialType)
            ->first();

        if ($row === null) {
            return null;
        }

        return $this->cipher->decrypt((string) $row->encrypted_value);
    }

    public function delete(string $integrationId, string $credentialType): void
    {
        DB::table('integration_credentials')
            ->where('integration_id', $integrationId)
            ->where('credential_type', $credentialType)
            ->delete();
    }
}
