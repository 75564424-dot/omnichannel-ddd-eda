<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers\Web;

use App\Control\Application\Services\ClientIncidentReportService;
use App\Control\Application\Services\ClientSupportWebService;
use App\Models\User;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SupportNotificationsWebController
{
    public function __construct(
        private readonly ClientIncidentReportService $reports,
        private readonly ClientSupportWebService $support,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->reports->inboxForUser($user));
    }

    public function show(Request $request, ClientIncidentReportModel $report): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $payload = $this->support->showReportForClient($user, $report);
        if ($payload === null) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        return response()->json($payload);
    }

    public function markRead(Request $request, ClientIncidentReportModel $report): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->support->markReportRead($user, $report));
    }

    public function markAllRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->support->markAllRead($user));
    }
}
