<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Application\Security\OperatorSessionTerminator;
use App\Models\User;
use App\Shared\Identity\Application\AuthenticateOperatorUseCase;
use App\Shared\Identity\Application\ResolveOperatorHomePathUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController
{
    public function __construct(
        private readonly ResolveOperatorHomePathUseCase $homePath,
        private readonly OperatorSessionTerminator $sessionTerminator,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return redirect()->route('dashboard');
        }

        if ($request->user() !== null) {
            return redirect()->to($this->homePath->execute($request->user()));
        }

        return Inertia::render('Auth/Login');
    }

    public function store(Request $request, AuthenticateOperatorUseCase $authenticate): RedirectResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $result = $authenticate->execute(
            $validated['email'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false),
        );

        if (! $result['success']) {
            return back()->withErrors(['email' => $result['error']])->onlyInput('email');
        }

        $user = $request->user();
        $intended = $request->session()->pull('url.intended');
        if (is_string($intended) && $intended !== '') {
            return redirect()->to($intended);
        }

        return redirect()->to($user instanceof User ? $this->homePath->execute($user) : route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->sessionTerminator->terminate($request);

        return redirect()->route('login');
    }
}
