<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Observability\Application\Services\SliMetricsRecorder;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class SliMetricsRecorderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function record_persists_sli_metric_row(): void
    {
        $tenantId = Uuid::uuid4()->toString();
        $tenant = $this->createMock(InstanceTenantContextInterface::class);
        $tenant->method('tenantId')->willReturn($tenantId);

        (new SliMetricsRecorder($tenant))->record('publish_success', 1.0, ['event_type' => 'Test.Event']);

        $row = DB::table('observability_metrics')->first();
        $this->assertNotNull($row);
        $this->assertSame($tenantId, $row->tenant_id);
        $this->assertSame('sli', $row->metric_scope);
        $this->assertSame('publish_success', $row->metric_key);
        $this->assertSame(1.0, (float) $row->metric_value);
    }
}
