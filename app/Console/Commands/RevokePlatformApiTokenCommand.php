<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

final class RevokePlatformApiTokenCommand extends Command
{
    protected $signature = 'platform:revoke-api-token {token-id : personal_access_tokens.id}';

    protected $description = 'Revokes a Sanctum personal access token by ID';

    public function handle(): int
    {
        $token = PersonalAccessToken::query()->find((int) $this->argument('token-id'));
        if ($token === null) {
            $this->error('Token not found.');

            return self::FAILURE;
        }

        $name = $token->name;
        $token->delete();

        $this->info("Revoked token #{$this->argument('token-id')} ({$name}).");

        return self::SUCCESS;
    }
}
