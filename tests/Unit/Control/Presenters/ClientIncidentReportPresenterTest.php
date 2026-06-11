<?php

declare(strict_types=1);

namespace Tests\Unit\Control\Presenters;

use App\Control\Application\Presenters\ClientIncidentReportPresenter;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientIncidentReportPresenterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function status_and_severity_labels_match_spanish_contract(): void
    {
        $this->assertSame('Abierto', ClientIncidentReportPresenter::statusLabel('open'));
        $this->assertSame('En revisión', ClientIncidentReportPresenter::statusLabel('acknowledged'));
        $this->assertSame('Resuelto', ClientIncidentReportPresenter::statusLabel('resolved'));
        $this->assertSame('Alta', ClientIncidentReportPresenter::severityLabel('high'));
    }

    #[Test]
    public function to_client_inbox_preserves_response_and_unread_flags(): void
    {
        $report = new ClientIncidentReportModel([
            'id'              => '11111111-1111-1111-1111-111111111111',
            'subject'         => 'Bus detenido',
            'description'     => 'No procesa eventos',
            'severity'        => 'high',
            'status'          => 'acknowledged',
            'admin_response'  => 'Revisamos el bus.',
            'responded_by_name' => 'SaaS',
            'responded_at'    => now(),
            'created_at'      => now(),
        ]);

        $row = (new ClientIncidentReportPresenter())->toClientInbox($report);

        $this->assertSame('Bus detenido', $row['subject']);
        $this->assertTrue($row['has_response']);
        $this->assertTrue($row['unread']);
        $this->assertSame('En revisión', $row['status_label']);
        $this->assertSame('Alta', $row['severity_label']);
    }

    #[Test]
    public function to_control_presentation_includes_diagnostic_summary(): void
    {
        $report = new ClientIncidentReportModel([
            'id'             => '22222222-2222-2222-2222-222222222222',
            'reporter_email' => 'client@local',
            'tenant_name'    => 'Acme',
            'subject'        => 'Integración',
            'description'    => 'Fallo',
            'severity'       => 'normal',
            'status'         => 'open',
            'diagnostic_log' => [
                'bus' => ['status' => 'STOPPED'],
                'active_alerts' => [['name' => 'BusStopped']],
                'recent_failures' => [['id' => 1], ['id' => 2]],
            ],
            'created_at'     => now(),
        ]);

        $row = (new ClientIncidentReportPresenter())->toControlPresentation($report);

        $this->assertSame('client_report', $row['type']);
        $this->assertSame('Acme · client@local', $row['client_label']);
        $this->assertSame('STOPPED', $row['diagnostic_summary']['bus_status']);
        $this->assertSame(1, $row['diagnostic_summary']['alerts_at_capture']);
        $this->assertSame(2, $row['diagnostic_summary']['failures_at_capture']);
    }
}
