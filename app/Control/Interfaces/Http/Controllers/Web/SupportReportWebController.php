<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers\Web;

use App\Control\Application\Services\ClientSupportWebService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Support reports from the client portal (web session — no Sanctum stateful domain required).
 */
final class SupportReportWebController
{
    public function __construct(
        private readonly ClientSupportWebService $support,
    ) {}

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'severity'    => ['nullable', 'string', 'in:low,normal,high,critical'],
            'subject'     => ['nullable', 'string', 'max:160'],
            'page_url'    => ['nullable', 'string', 'max:500'],
        ]);

        $payload = $this->support->createReportResponse($user, $validated);

        return response()->json($payload, 201);
    }
}
