<?php

declare(strict_types=1);

namespace App\Shared\Identity\Application;

use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\Auth;

final class AuthenticateOperatorUseCase
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceContext,
    ) {}

    /**
     * @return array{success: bool, user: ?User, error: ?string}
     */
    public function execute(string $email, string $password, bool $remember = false): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return [
                'success' => false,
                'user'    => null,
                'error'   => 'Invalid credentials.',
            ];
        }

        /** @var User $user */
        $user = Auth::user();
        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));

        if ($role !== null && $role->isInstanceOperator()) {
            $userTenantId = $user->getAttribute('tenant_id');

            if ($userTenantId === null) {
                Auth::logout();

                return [
                    'success' => false,
                    'user'    => null,
                    'error'   => 'Su cuenta no está asignada a una empresa. Contacte al administrador SaaS.',
                ];
            }

            if ($this->instanceContext->allowsMultiTenantPortalLogin()) {
                $this->instanceContext->bindPortalTenantFromSession((string) $userTenantId);
            } else {
                $instanceTenantId = $this->instanceContext->configuredInstanceTenantId();

                if ($instanceTenantId !== null && (string) $userTenantId !== $instanceTenantId) {
                    Auth::logout();

                    return [
                        'success' => false,
                        'user'    => null,
                        'error'   => 'Su cuenta pertenece a otra instancia. Use la URL de su empresa (PLATFORM_CLIENT_SLUG dedicado) o contacte al administrador SaaS.',
                    ];
                }
            }
        }

        session()->regenerate();

        return [
            'success' => true,
            'user'    => $user,
            'error'   => null,
        ];
    }

}
