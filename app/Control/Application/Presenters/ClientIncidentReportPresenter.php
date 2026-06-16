<?php

declare(strict_types=1);

namespace App\Control\Application\Presenters;

use App\Shared\Infrastructure\Models\ClientIncidentReportModel;

final class ClientIncidentReportPresenter
{
    public static function statusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'acknowledged' => 'En revisión',
            'resolved'     => 'Resuelto',
            default        => 'Abierto',
        };
    }

    public static function severityLabel(string $severity): string
    {
        return match (strtolower($severity)) {
            'low'      => 'Baja',
            'high'     => 'Alta',
            'critical' => 'Crítica',
            default    => 'Normal',
        };
    }

    /** @return array<string, mixed> */
    public function toClientInbox(ClientIncidentReportModel $report): array
    {
        $hasResponse = $report->admin_response !== null && $report->admin_response !== '';

        return [
            'id'                => $report->id,
            'subject'           => $report->subject,
            'description'       => $report->description,
            'severity'          => $report->severity,
            'severity_label'    => self::severityLabel($report->severity),
            'status'            => $report->status,
            'status_label'      => self::statusLabel($report->status),
            'has_response'      => $hasResponse,
            'unread'            => $hasResponse && $report->hasUnreadResponseForClient(),
            'admin_response'    => $hasResponse ? $report->admin_response : null,
            'responded_by_name' => $report->responded_by_name,
            'responded_at'      => $report->responded_at?->toDateTimeString(),
            'created_at'        => $report->created_at?->toDateTimeString(),
        ];
    }

    /** @return array<string, mixed> */
    public function toClientDetail(ClientIncidentReportModel $report): array
    {
        $hasResponse = $report->admin_response !== null && $report->admin_response !== '';

        return array_merge($this->toClientInbox($report), [
            'page_url'       => $report->page_url,
            'diagnostic_log' => is_array($report->diagnostic_log) ? $report->diagnostic_log : [],
            'client_read_at' => $report->client_read_at?->toDateTimeString(),
        ]);
    }

    /** @return array<string, mixed> */
    public function toControlPresentation(ClientIncidentReportModel $report): array
    {
        $log = is_array($report->diagnostic_log) ? $report->diagnostic_log : [];
        $alertCount = is_array($log['active_alerts'] ?? null) ? count($log['active_alerts']) : 0;
        $failureCount = is_array($log['recent_failures'] ?? null) ? count($log['recent_failures']) : 0;

        return [
            'id'               => $report->id,
            'type'             => 'client_report',
            'tenant_id'        => $report->tenant_id,
            'tenant_name'      => $report->tenant_name,
            'tenant_slug'      => $report->tenant_slug,
            'client_label'     => $report->tenant_name
                ? $report->tenant_name.' · '.$report->reporter_email
                : $report->reporter_email,
            'reporter_name'    => $report->reporter_name,
            'reporter_email'   => $report->reporter_email,
            'subject'          => $report->subject,
            'description'      => $report->description,
            'severity'         => $report->severity,
            'status'           => $report->status,
            'page_url'         => $report->page_url,
            'diagnostic_log'   => $log,
            'diagnostic_summary' => [
                'bus_status'          => $log['bus']['status'] ?? null,
                'alerts_at_capture'   => $alertCount,
                'failures_at_capture' => $failureCount,
            ],
            'created_at'       => $report->created_at?->toDateTimeString(),
            'acknowledged_at'  => $report->acknowledged_at?->toDateTimeString(),
            'resolved_at'      => $report->resolved_at?->toDateTimeString(),
            'admin_response'   => $report->admin_response,
            'responded_by_name'=> $report->responded_by_name,
            'responded_at'     => $report->responded_at?->toDateTimeString(),
            'has_response'     => $report->admin_response !== null && $report->admin_response !== '',
            'client_read_at'   => $report->client_read_at?->toDateTimeString(),
        ];
    }
}
