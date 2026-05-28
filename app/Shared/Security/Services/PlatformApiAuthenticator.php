<?php

declare(strict_types=1);

namespace App\Shared\Security\Services;

use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Security\Contracts\PlatformApiAuthenticatorInterface;
use App\Shared\Security\PlatformApiPrincipal;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class PlatformApiAuthenticator implements PlatformApiAuthenticatorInterface
{
    public function __construct(
        private readonly PlatformAuthorizationServiceInterface $authorization,
    ) {}

    public function authenticate(Request $request): ?PlatformApiPrincipal
    {
        $bearer = $request->bearerToken();
        if (is_string($bearer) && $bearer !== '') {
            $principal = $this->fromSanctumToken($bearer);
            if ($principal !== null) {
                return $principal;
            }
        }

        $apiKey = $request->header('X-API-Key');
        if (! is_string($apiKey) || $apiKey === '') {
            $apiKey = $request->query('token');
        }

        if (is_string($apiKey) && $apiKey !== '') {
            return $this->fromStaticApiKey($apiKey);
        }

        return $this->fromOperatorSession($request);
    }

    private function fromOperatorSession(Request $request): ?PlatformApiPrincipal
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return null;
        }

        /** @var list<string> $abilities */
        $abilities = $this->authorization->abilitiesForUser($user);

        return new PlatformApiPrincipal(
            actorType: 'operator_session',
            actorId: (string) $user->getKey(),
            abilities: $abilities,
            label: (string) $user->getAttribute('email').' ('.$this->authorization->roleForUser($user)->value.')',
        );
    }

    private function fromSanctumToken(string $token): ?PlatformApiPrincipal
    {
        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken === null) {
            return null;
        }

        /** @var mixed $tokenableRelation */
        $tokenableRelation = $accessToken->getRelationValue('tokenable');
        if (! $tokenableRelation instanceof User) {
            $accessToken->load('tokenable');
            $tokenableRelation = $accessToken->getRelationValue('tokenable');
        }

        if (! $tokenableRelation instanceof User) {
            return null;
        }

        /** @var list<string> $abilities */
        $abilities = $accessToken->abilities ?? ['*'];
        if ($abilities === ['*']) {
            $abilities = $this->allAbilities();
        }

        return new PlatformApiPrincipal(
            actorType: 'sanctum_token',
            actorId: (string) $accessToken->getKey(),
            abilities: $abilities,
            label: $tokenableRelation->getAttribute('email'),
        );
    }

    private function fromStaticApiKey(string $providedKey): ?PlatformApiPrincipal
    {
        foreach ($this->parsedStaticKeys() as $entry) {
            if (! hash_equals($entry['key'], $providedKey)) {
                continue;
            }

            return new PlatformApiPrincipal(
                actorType: 'api_key',
                actorId: $entry['id'],
                abilities: $entry['abilities'],
                label: $entry['label'],
            );
        }

        return null;
    }

    /**
     * @return list<array{key: string, id: string, label: string, abilities: list<string>}>
     */
    private function parsedStaticKeys(): array
    {
        $raw = trim((string) config('security.api_keys', ''));
        if ($raw === '') {
            return [];
        }

        $entries = [];
        foreach (explode(';', $raw) as $index => $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            $parts = explode('|', $segment, 2);
            $key = trim($parts[0]);
            if ($key === '') {
                continue;
            }

            $abilities = isset($parts[1]) && trim($parts[1]) !== ''
                ? array_values(array_filter(array_map('trim', explode(',', $parts[1]))))
                : $this->allAbilities();

            $entries[] = [
                'key'       => $key,
                'id'        => 'static-key-'.($index + 1),
                'label'     => 'static-api-key-'.($index + 1),
                'abilities' => $abilities,
            ];
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function allAbilities(): array
    {
        return [
            'events:publish',
            'bus:read',
            'bus:admin',
            'dashboard:read',
            'integrations:admin',
        ];
    }
}
