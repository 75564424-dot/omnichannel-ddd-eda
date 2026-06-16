<?php

declare(strict_types=1);

namespace App\Simulation\Interfaces\Http\Controllers;

use App\Simulation\Application\Services\Runtime\SimulationPulseService;
use Illuminate\Http\JsonResponse;

final class SimulationPulseController
{
    public function __construct(
        private readonly SimulationPulseService $pulse,
    ) {}

    public function index(): JsonResponse
    {
        return response()
            ->json(['data' => $this->pulse->snapshot()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
