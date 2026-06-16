<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Platform;

use App\Shared\Platform\LocalFleet\LocalFleetAppKeyResolver;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LocalFleetAppKeyResolverTest extends TestCase
{
    #[Test]
    public function reuses_existing_app_key_from_env_file(): void
    {
        $envId = 'test-env-'.Str::uuid();
        $envPath = base_path('.env.'.$envId);
        file_put_contents($envPath, "APP_KEY=base64:existing-key\n");

        try {
            $this->assertSame('base64:existing-key', (new LocalFleetAppKeyResolver())->resolve($envId));
        } finally {
            @unlink($envPath);
        }
    }

    #[Test]
    public function generates_base64_key_when_env_file_missing(): void
    {
        $key = (new LocalFleetAppKeyResolver())->resolve('missing-env-'.Str::uuid());

        $this->assertStringStartsWith('base64:', $key);
        $this->assertGreaterThan(20, strlen($key));
    }
}
