<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Models\User;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;

final class ClientSupportWebService
{
    public function __construct(
        private readonly ClientIncidentReportService $reports,
    ) {}

    /** @return array<string, mixed> */
    public function createReportResponse(User $user, array $validated): array
    {
        $report = $this->reports->createFromClient(
            $user,
            $validated['description'],
            $validated['severity'] ?? 'normal',
            $validated['subject'] ?? null,
            $validated['page_url'] ?? null,
        );

        return [
            'message' => 'Reporte enviado. Nuestro equipo lo revisará pronto.',
            'report'  => [
                'id'         => $report->id,
                'subject'    => $report->subject,
                'status'     => $report->status,
                'created_at' => $report->created_at?->toDateTimeString(),
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    public function showReportForClient(User $user, ClientIncidentReportModel $report): ?array
    {
        $detail = $this->reports->findForClient($user, $report->id);
        if ($detail === null) {
            return null;
        }

        if ($detail['has_response']) {
            $this->reports->markAsReadByClient($report, $user);
            $detail['unread'] = false;
            $detail['client_read_at'] = now()->toDateTimeString();
        }

        return [
            'report'       => $detail,
            'unread_count' => $this->reports->unreadResponsesCountForUser($user),
        ];
    }

    /** @return array{unread_count: int} */
    public function markReportRead(User $user, ClientIncidentReportModel $report): array
    {
        $this->reports->markAsReadByClient($report, $user);

        return ['unread_count' => $this->reports->unreadResponsesCountForUser($user)];
    }

    /** @return array{unread_count: int} */
    public function markAllRead(User $user): array
    {
        $this->reports->markAllReadByClient($user);

        return ['unread_count' => 0];
    }
}
