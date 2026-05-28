<?php

declare(strict_types=1);

namespace App\Shared\Security;

/**
 * Authenticated API caller resolved from Sanctum token or static API key.
 */
final class PlatformApiPrincipal
{
    /**
     * @param list<string> $abilities
     */
    public function __construct(
        public readonly string $actorType,
        public readonly string $actorId,
        public readonly array $abilities,
        public readonly ?string $label = null,
    ) {}

    public function hasAbility(string $ability): bool
    {
        return in_array($ability, $this->abilities, true);
    }

    /**
     * @param list<string> $abilities
     */
    public function hasAnyAbility(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->hasAbility($ability)) {
                return true;
            }
        }

        return false;
    }
}
