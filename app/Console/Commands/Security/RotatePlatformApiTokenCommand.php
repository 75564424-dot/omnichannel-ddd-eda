<?php

declare(strict_types=1);

namespace App\Console\Commands\Security;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

final class RotatePlatformApiTokenCommand extends Command
{
    protected $signature = 'platform:rotate-api-token
                            {--email=platform-service@local : Service user email}
                            {--name=Platform Service Rotated : New token name}
                            {--abilities=events:publish,bus:read,bus:admin,dashboard:read : Comma-separated abilities}
                            {--keep=0 : Number of most recent tokens to keep after rotation}';

    protected $description = 'Revokes existing Sanctum tokens for the service user and issues a new one (Plan_Seguridad Fase 3)';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error("User not found: {$email}. Run platform:issue-api-token first.");

            return self::FAILURE;
        }

        $keep = max(0, (int) $this->option('keep'));
        $tokens = PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $toRevoke = $tokens->slice($keep);
        foreach ($toRevoke as $token) {
            $token->delete();
        }

        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('abilities')))));
        $newToken = $user->createToken((string) $this->option('name'), $abilities);

        $this->info('Rotated platform API token. Revoked '.count($toRevoke).' token(s).');
        $this->line('New token (store securely — shown once):');
        $this->line($newToken->plainTextToken);

        return self::SUCCESS;
    }
}
