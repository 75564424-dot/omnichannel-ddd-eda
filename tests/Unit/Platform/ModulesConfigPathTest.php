<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\Support\ModulesConfigPath;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModulesConfigPathTest extends TestCase
{
    #[Test]
    public function resolves_relative_modules_config_path_from_env(): void
    {
        putenv('MODULES_CONFIG_PATH=config/modules/instances/pruebas-retail/modules_config.json');

        $this->assertSame(
            base_path('config/modules/instances/pruebas-retail/modules_config.json'),
            ModulesConfigPath::resolve(),
        );
    }

    #[Test]
    public function falls_back_to_default_when_env_missing(): void
    {
        putenv('MODULES_CONFIG_PATH');

        $this->assertSame(
            config_path('modules/modules_config.json'),
            ModulesConfigPath::resolve(),
        );
    }
}
