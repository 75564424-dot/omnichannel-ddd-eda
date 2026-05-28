<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\ClientIncidentReportService;
use App\Control\Application\Services\ControlIncidentsService;
use App\Shared\Infrastructure\Models\ClientIncidentReportModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final class IncidentsController
{
    public function __construct(
        private readonly ControlIncidentsService $incidents,
        private readonly ClientIncidentReportService $reports,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Control/Incidents/Index', $this->incidents->buildDashboard());
    }

    public function showReport(ClientIncidentReportModel $report): Response
    {
        $detail = $this->reports->findForControl($report->id);
        if ($detail === null) {
            abort(404);
        }

        return Inertia::render('Control/Incidents/ReportShow', [
            'report' => $detail,
        ]);
    }

    public function updateReport(Request $request, ClientIncidentReportModel $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['open', 'acknowledged', 'resolved'])],
        ]);

        $this->reports->updateStatus($report, $validated['status']);

        return redirect()
            ->route('control.incidents')
            ->with('message', 'Estado del reporte actualizado.');
    }

    public function respondReport(Request $request, ClientIncidentReportModel $report): RedirectResponse
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $user = $request->user();
        $name = $user !== null ? (string) $user->getAttribute('name') : 'SaaS Support';

        $this->reports->respond($report, $validated['admin_response'], $name);

        return redirect()
            ->back()
            ->with('message', 'Respuesta enviada al cliente. Verá una notificación en su portal.');
    }
}
