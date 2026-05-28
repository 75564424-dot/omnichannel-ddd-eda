<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Models\User;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

final class ClientIncidentReportService
{
    public function __construct(
        private readonly IncidentDiagnosticCollector $diagnostics,
    ) {}

    /**
     * @param array<string, mixed> $clientContext
     */
    public function createFromClient(
        User $user,
        string $description,
        string $severity = 'normal',
        ?string $subject = null,
        ?string $pageUrl = null,
        array $clientContext = [],
    ): ClientIncidentReportModel {
        $tenant = $this->resolveInstanceTenant();

        $context = array_merge($clientContext, [
            'page_url'    => $pageUrl,
            'user_agent'  => request()->userAgent(),
            'reporter_ip' => request()->ip(),
        ]);

        return ClientIncidentReportModel::query()->create([
            'id'              => Uuid::uuid4()->toString(),
            'tenant_id'       => $tenant?->id,
            'user_id'         => $user->getKey(),
            'reporter_name'   => (string) $user->getAttribute('name'),
            'reporter_email'  => (string) $user->getAttribute('email'),
            'tenant_name'     => $tenant?->name,
            'tenant_slug'     => $tenant?->slug,
            'subject'         => $subject !== null && $subject !== ''
                ? $subject
                : 'Reporte de soporte — '.now()->format('Y-m-d H:i'),
            'description'     => $description,
            'severity'        => $this->normalizeSeverity($severity),
            'status'          => 'open',
            'page_url'        => $pageUrl,
            'diagnostic_log'  => $this->diagnostics->collect($context),
        ]);
    }

    public function respond(
        ClientIncidentReportModel $report,
        string $message,
        string $responderName,
    ): ClientIncidentReportModel {
        $report->update([
            'admin_response'      => $message,
            'responded_by_name'   => $responderName,
            'responded_at'        => now(),
            'client_read_at'      => null,
            'status'              => $report->status === 'open' ? 'acknowledged' : $report->status,
            'acknowledged_at'     => $report->acknowledged_at ?? now(),
        ]);

        return $report->fresh() ?? $report;
    }

    public function findForControl(string $id): ?array
    {
        $report = ClientIncidentReportModel::query()->find($id);

        return $report !== null ? $this->toPresentation($report) : null;
    }

    public function unreadResponsesCountForUser(User $user): int
    {
        return (int) ClientIncidentReportModel::query()
            ->where('user_id', $user->getKey())
            ->whereNotNull('admin_response')
            ->where('admin_response', '!=', '')
            ->whereNull('client_read_at')
            ->count();
    }

    /** @return array<string, mixed> */
    public function inboxForUser(User $user, int $limit = 50): array
    {
        $reports = ClientIncidentReportModel::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ClientIncidentReportModel $r) => $this->toClientInbox($r))
            ->all();

        $pending = 0;
        $answered = 0;
        foreach ($reports as $row) {
            if ($row['has_response']) {
                $answered++;
            } else {
                $pending++;
            }
        }

        return [
            'reports'      => $reports,
            'unread_count' => $this->unreadResponsesCountForUser($user),
            'summary'      => [
                'total'    => count($reports),
                'pending'  => $pending,
                'answered' => $answered,
                'unread'   => $this->unreadResponsesCountForUser($user),
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function findForClient(User $user, string $reportId): ?array
    {
        $report = ClientIncidentReportModel::query()
            ->where('user_id', $user->getKey())
            ->find($reportId);

        return $report !== null ? $this->toClientDetail($report) : null;
    }

    public function markAsReadByClient(ClientIncidentReportModel $report, User $user): void
    {
        if ((int) $report->user_id !== (int) $user->getKey()) {
            return;
        }

        if ($report->admin_response === null || $report->admin_response === '') {
            return;
        }

        $report->update(['client_read_at' => now()]);
    }

    public function markAllReadByClient(User $user): void
    {
        ClientIncidentReportModel::query()
            ->where('user_id', $user->getKey())
            ->whereNotNull('admin_response')
            ->whereNull('client_read_at')
            ->update(['client_read_at' => now()]);
    }

    public function updateStatus(ClientIncidentReportModel $report, string $status): ClientIncidentReportModel
    {
        $status = strtolower($status);
        $payload = ['status' => $status];

        if ($status === 'acknowledged' && $report->acknowledged_at === null) {
            $payload['acknowledged_at'] = now();
        }
        if ($status === 'resolved' && $report->resolved_at === null) {
            $payload['resolved_at'] = now();
            $payload['acknowledged_at'] = $report->acknowledged_at ?? now();
        }

        $report->update($payload);

        return $report->fresh() ?? $report;
    }

    /** @return list<array<string, mixed>> */
    public function listForControl(int $limit = 50): array
    {
        return ClientIncidentReportModel::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ClientIncidentReportModel $r) => $this->toPresentation($r))
            ->all();
    }

    /** @return array<string, mixed> */
    public function summaryCounts(): array
    {
        $open = (int) ClientIncidentReportModel::query()->where('status', 'open')->count();
        $ack = (int) ClientIncidentReportModel::query()->where('status', 'acknowledged')->count();
        $resolved = (int) ClientIncidentReportModel::query()->where('status', 'resolved')->count();
        $last24h = (int) ClientIncidentReportModel::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'open'         => $open,
            'acknowledged' => $ack,
            'resolved'     => $resolved,
            'last_24h'     => $last24h,
            'total'        => $open + $ack + $resolved,
        ];
    }

    private function resolveInstanceTenant(): ?TenantModel
    {
        $slug = Str::slug((string) config('platform.client_slug', ''));

        if ($slug !== '') {
            $bySlug = TenantModel::query()->where('slug', $slug)->first();
            if ($bySlug !== null) {
                return $bySlug;
            }
        }

        return TenantModel::query()->orderBy('created_at')->first();
    }

    private function normalizeSeverity(string $severity): string
    {
        $severity = strtolower($severity);

        return in_array($severity, ['low', 'normal', 'high', 'critical'], true) ? $severity : 'normal';
    }

    private static function statusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'acknowledged' => 'En revisión',
            'resolved'     => 'Resuelto',
            default        => 'Abierto',
        };
    }

    private static function severityLabel(string $severity): string
    {
        return match (strtolower($severity)) {
            'low'      => 'Baja',
            'high'     => 'Alta',
            'critical' => 'Crítica',
            default    => 'Normal',
        };
    }

    /** @return array<string, mixed> */
    private function toClientInbox(ClientIncidentReportModel $report): array
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
    private function toClientDetail(ClientIncidentReportModel $report): array
    {
        $hasResponse = $report->admin_response !== null && $report->admin_response !== '';

        return array_merge($this->toClientInbox($report), [
            'page_url'       => $report->page_url,
            'diagnostic_log' => is_array($report->diagnostic_log) ? $report->diagnostic_log : [],
            'client_read_at' => $report->client_read_at?->toDateTimeString(),
        ]);
    }

    /** @return array<string, mixed> */
    private function toPresentation(ClientIncidentReportModel $report): array
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
                'bus_status'      => $log['bus']['status'] ?? null,
                'alerts_at_capture' => $alertCount,
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
