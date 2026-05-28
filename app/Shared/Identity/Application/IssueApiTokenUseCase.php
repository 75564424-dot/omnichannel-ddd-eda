<?php

declare(strict_types=1);

namespace App\Shared\Identity\Application;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

final class IssueApiTokenUseCase
{
    /**
     * @param list<string> $abilities
     */
    public function execute(string $email, string $tokenName, array $abilities, ?string $userName = null): NewAccessToken
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name'     => $userName ?? 'Platform Service Account',
                'password' => Hash::make(Str::password(32)),
            ]
        );

        return $user->createToken($tokenName, $abilities);
    }
}
