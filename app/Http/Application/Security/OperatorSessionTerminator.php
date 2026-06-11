<?php

declare(strict_types=1);

namespace App\Http\Application\Security;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

/**
 * Terminates authenticated web sessions consistently across Http middleware.
 */
final class OperatorSessionTerminator
{
    public function __construct(
        private readonly AuthFactory $auth,
    ) {}

    public function terminate(Request $request): void
    {
        $this->auth->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
