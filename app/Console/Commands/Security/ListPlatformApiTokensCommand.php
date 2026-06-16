<?php

declare(strict_types=1);

namespace App\Console\Commands\Security;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

final class ListPlatformApiTokensCommand extends Command
{
    protected $signature = 'platform:list-api-tokens {--email= : Filter by user email}';

    protected $description = 'Lists Sanctum personal access tokens (metadata only)';

    public function handle(): int
    {
        $query = PersonalAccessToken::query()->with('tokenable');

        if ($email = $this->option('email')) {
            $userIds = User::query()->where('email', $email)->pluck('id');
            $query->whereIn('tokenable_id', $userIds)->where('tokenable_type', User::class);
        }

        $rows = $query->orderByDesc('id')->get();

        if ($rows->isEmpty()) {
            $this->warn('No API tokens found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'User', 'Abilities', 'Last used', 'Created'],
            $rows->map(fn (PersonalAccessToken $token) => [
                $token->getKey(),
                $token->name,
                $token->tokenable instanceof User ? $token->tokenable->getAttribute('email') : 'n/a',
                implode(', ', $token->abilities ?? []),
                $token->last_used_at?->toDateTimeString() ?? '—',
                $token->created_at?->toDateTimeString() ?? '—',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
