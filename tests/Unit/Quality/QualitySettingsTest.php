<?php

declare(strict_types=1);

namespace Tests\Unit\Quality;

use App\Quality\Application\Services\QualitySettings;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class QualitySettingsTest extends TestCase
{
    #[Test]
    public function from_config_reads_platform_quality_defaults(): void
    {
        config([
            'platform_quality' => [
                'coverage' => [
                    'application_min_percent' => 85,
                    'clover_path' => 'custom/clover.xml',
                ],
                'load_test' => [
                    'target_eps' => 200,
                    'event_type' => 'Custom.LoadTest',
                ],
            ],
        ]);

        $settings = QualitySettings::fromConfig();

        $this->assertSame(85, $settings->coverageMinPercent());
        $this->assertSame('custom/clover.xml', $settings->cloverPath());
        $this->assertSame(200, $settings->loadTestTargetEps());
        $this->assertSame('Custom.LoadTest', $settings->loadTestEventType());
    }

    #[Test]
    public function defaults_apply_when_config_keys_are_missing(): void
    {
        config(['platform_quality' => []]);

        $settings = QualitySettings::fromConfig();

        $this->assertSame(70, $settings->coverageMinPercent());
        $this->assertSame('build/coverage/clover.xml', $settings->cloverPath());
    }
}
