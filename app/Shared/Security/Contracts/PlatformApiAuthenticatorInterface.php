<?php

declare(strict_types=1);

namespace App\Shared\Security\Contracts;

use App\Shared\Security\PlatformApiPrincipal;
use Illuminate\Http\Request;

interface PlatformApiAuthenticatorInterface
{
    public function authenticate(Request $request): ?PlatformApiPrincipal;
}
