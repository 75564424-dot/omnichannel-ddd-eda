<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers;

use App\Control\Application\Services\ClientIncidentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientSupportReportApiController
{
    public function __construct(
        private readonly ClientIncidentReportService $reports,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'severity'    => ['nullable', 'string', 'in:low,normal,high,critical'],
            'subject'     => ['nullable', 'string', 'max:160'],
            'page_url'    => ['nullable', 'string', 'max:500'],
        ]);

        $report = $this->reports->createFromClient(
            $user,
            $validated['description'],
            $validated['severity'] ?? 'normal',
            $validated['subject'] ?? null,
            $validated['page_url'] ?? null,
        );

        return response()->json([
            'message' => 'Reporte enviado. Nuestro equipo lo revisará pronto.',
            'report'  => [
                'id'         => $report->id,
                'subject'    => $report->subject,
                'status'     => $report->status,
                'created_at' => $report->created_at?->toDateTimeString(),
            ],
        ], 201);
    }
}
