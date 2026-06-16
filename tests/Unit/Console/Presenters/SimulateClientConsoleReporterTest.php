<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Presenters;

use App\Console\Application\Presenters\SimulateClientConsoleReporter;
use App\Console\Application\Services\Simulation\SimulateClientCommandOptions;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

final class SimulateClientConsoleReporterTest extends TestCase
{
    #[Test]
    public function report_fixture_not_found_writes_error_and_returns_failure(): void
    {
        [$command, $output] = $this->command();

        $exit = (new SimulateClientConsoleReporter())->reportFixtureNotFound(
            $command,
            'missing',
            ['retailco', 'acmepos'],
        );

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('Fixture [missing] not found', $output->fetch());
    }

    #[Test]
    public function report_simulation_result_returns_failure_on_validation_errors(): void
    {
        [$command, $output] = $this->command();

        $exit = (new SimulateClientConsoleReporter())->reportSimulationResult($command, 'retailco', [
            'validation_errors' => ['bad route'],
            'sync'              => null,
            'published'         => 0,
            'queue_matches'     => 0,
        ]);

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('Catalog validation failed', $output->fetch());
    }

    #[Test]
    public function report_publish_plan_outputs_rate_summary(): void
    {
        [$command, $output] = $this->command();
        $options = new SimulateClientCommandOptions(
            slug: 'retailco',
            events: 0,
            perMinute: 10,
            durationMinutes: 2,
            applyFixture: false,
            skipSync: false,
            skipValidate: false,
        );

        (new SimulateClientConsoleReporter())->reportPublishPlan($command, $options, [
            'total'                 => 20,
            'interval_microseconds' => 6_000_000,
        ]);

        $this->assertStringContainsString('Publishing 20 event(s) at 10/min', $output->fetch());
    }

    /**
     * @return array{0: Command, 1: BufferedOutput}
     */
    private function command(): array
    {
        $command = new Command('test');
        $output = new BufferedOutput();
        $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

        return [$command, $output];
    }
}
