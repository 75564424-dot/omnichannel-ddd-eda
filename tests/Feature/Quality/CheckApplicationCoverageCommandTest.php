<?php

declare(strict_types=1);

namespace Tests\Feature\Quality;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CheckApplicationCoverageCommandTest extends TestCase
{
    #[Test]
    public function quality_coverage_command_passes_on_fixture_clover(): void
    {
        $this->artisan('platform:quality-coverage', [
            '--clover' => base_path('tests/Fixtures/quality/sample-clover.xml'),
            '--min' => 70,
        ])->assertSuccessful();
    }

    #[Test]
    public function quality_coverage_json_outputs_gate_result(): void
    {
        $this->artisan('platform:quality-coverage', [
            '--clover' => base_path('tests/Fixtures/quality/sample-clover.xml'),
            '--min' => 70,
            '--json' => true,
        ])
            ->expectsOutputToContain('"passed":true')
            ->assertSuccessful();
    }
}
