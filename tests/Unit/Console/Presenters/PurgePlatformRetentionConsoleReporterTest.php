<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Presenters;

use App\Console\Application\Presenters\PurgePlatformRetentionConsoleReporter;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

final class PurgePlatformRetentionConsoleReporterTest extends TestCase
{
    #[Test]
    public function report_invalid_table_returns_failure(): void
    {
        [$command, $output] = $this->command();

        $exit = (new PurgePlatformRetentionConsoleReporter())->reportInvalidTable($command);

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('Invalid --table value', $output->fetch());
    }

    #[Test]
    public function report_purge_results_renders_skipped_and_summary(): void
    {
        [$command, $output] = $this->command();

        $exit = (new PurgePlatformRetentionConsoleReporter())->reportPurgeResults($command, [
            'trace_logs' => ['days' => 0, 'deleted' => 0, 'cutoff' => null, 'skipped' => true],
            'message_queue' => ['days' => 30, 'deleted' => 2, 'cutoff' => '2026-01-01T00:00:00+00:00'],
        ], dryRun: true);

        $this->assertSame(Command::SUCCESS, $exit);
        $text = $output->fetch();
        $this->assertStringContainsString('[skip] trace_logs', $text);
        $this->assertStringContainsString('would delete 2 rows', $text);
        $this->assertStringContainsString('Dry run complete.', $text);
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
