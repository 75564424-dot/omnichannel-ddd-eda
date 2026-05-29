<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\Registrars\PlatformGateRegistrar;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PlatformGateRegistrarTest extends TestCase
{
    #[Test]
    public function gates_allow_all_when_api_auth_is_disabled(): void
    {
        config(['security.api_auth_enabled' => false, 'platform_auth.web_auth_enabled' => false]);

        PlatformGateRegistrar::register();

        $this->assertTrue(Gate::allows('platform.publish'));
        $this->assertTrue(Gate::allows('platform.sync-registry'));
        $this->assertTrue(Gate::allows('platform.manage-integrations'));
    }
}
