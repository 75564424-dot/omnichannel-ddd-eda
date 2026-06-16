<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Per-instance XSRF cookie names break default Laravel test POST flows (419).
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        $this->purgeSimulationRuntimeArtifacts();

        parent::tearDown();
    }

    private function purgeSimulationRuntimeArtifacts(): void
    {
        $handoffDir = storage_path('app/simulation-handoff');
        if (is_dir($handoffDir)) {
            foreach (glob($handoffDir.'/*') ?: [] as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        $pulse = storage_path('app/simulation-pulse.json');
        if (is_file($pulse)) {
            @unlink($pulse);
        }
    }
}
