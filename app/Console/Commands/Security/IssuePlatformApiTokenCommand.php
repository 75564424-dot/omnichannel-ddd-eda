<?php

declare(strict_types=1);

namespace App\Console\Commands\Security;

use App\Shared\Identity\Application\IssueApiTokenUseCase;
use Illuminate\Console\Command;

final class IssuePlatformApiTokenCommand extends Command
{
    protected $signature = 'platform:issue-api-token
                            {--email=platform-service@local : Service user email}
                            {--name=Platform Service : Token name}
                            {--abilities=events:publish,bus:read,bus:admin,dashboard:read : Comma-separated abilities}';

    protected $description = 'Issues a Sanctum personal access token for platform API access';

    public function handle(IssueApiTokenUseCase $issueApiToken): int
    {
        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('abilities')))));
        $token = $issueApiToken->execute(
            (string) $this->option('email'),
            (string) $this->option('name'),
            $abilities,
        );

        $this->info('Platform API token issued (store securely — shown once):');
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->line('Use as: Authorization: Bearer {token}');

        return self::SUCCESS;
    }
}
