<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Control\Application\Services\ClientIncidentReportService;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SupportNotificationsWebController
{
    public function __construct(
        private readonly ClientIncidentReportService $reports,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->reports->inboxForUser($user));
    }

    public function show(Request $request, ClientIncidentReportModel $report): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $detail = $this->reports->findForClient($user, $report->id);
        if ($detail === null) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        if ($detail['has_response']) {
            $this->reports->markAsReadByClient($report, $user);
            $detail['unread'] = false;
            $detail['client_read_at'] = now()->toDateTimeString();
        }

        return response()->json([
            'report'       => $detail,
            'unread_count' => $this->reports->unreadResponsesCountForUser($user),
        ]);
    }

    public function markRead(Request $request, ClientIncidentReportModel $report): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->reports->markAsReadByClient($report, $user);

        return response()->json([
            'unread_count' => $this->reports->unreadResponsesCountForUser($user),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $this->reports->markAllReadByClient($user);

        return response()->json(['unread_count' => 0]);
    }
}
