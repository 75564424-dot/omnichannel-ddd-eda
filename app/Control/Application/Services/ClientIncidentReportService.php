<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Application\Presenters\ClientIncidentReportPresenter;
use App\Control\Application\Services\Support\ClientIncidentReportSeverityNormalizer;
use App\Control\Application\Services\Support\ClientIncidentReportTenantResolver;
use App\Models\User;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

final class ClientIncidentReportService
{
    public function __construct(
        private readonly IncidentDiagnosticCollector $diagnostics,
        private readonly ClientIncidentReportTenantResolver $tenantResolver,
        private readonly ClientIncidentReportPresenter $presenter,
        private readonly ClientIncidentReportSeverityNormalizer $severityNormalizer,
        private readonly Request $request,
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
        $tenant = $this->tenantResolver->resolveInstanceTenant();

        $context = array_merge($clientContext, [
            'page_url'    => $pageUrl,
            'user_agent'  => $this->request->userAgent(),
            'reporter_ip' => $this->request->ip(),
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
            'severity'        => $this->severityNormalizer->normalize($severity),
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

        return $report !== null ? $this->presenter->toControlPresentation($report) : null;
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
            ->map(fn (ClientIncidentReportModel $r) => $this->presenter->toClientInbox($r))
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

        $unread = $this->unreadResponsesCountForUser($user);

        return [
            'reports'      => $reports,
            'unread_count' => $unread,
            'summary'      => [
                'total'    => count($reports),
                'pending'  => $pending,
                'answered' => $answered,
                'unread'   => $unread,
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function findForClient(User $user, string $reportId): ?array
    {
        $report = ClientIncidentReportModel::query()
            ->where('user_id', $user->getKey())
            ->find($reportId);

        return $report !== null ? $this->presenter->toClientDetail($report) : null;
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
            ->map(fn (ClientIncidentReportModel $r) => $this->presenter->toControlPresentation($r))
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
}
