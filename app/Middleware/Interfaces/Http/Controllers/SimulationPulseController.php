<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\Services\SimulationPulseService;
use Illuminate\Http\JsonResponse;

final class SimulationPulseController
{
    public function __construct(
        private readonly SimulationPulseService $pulse,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->pulse->snapshot()]);
    }
}
