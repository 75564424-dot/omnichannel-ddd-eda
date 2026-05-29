<?php

declare(strict_types=1);

namespace App\Control\Interfaces\Http\Controllers;

use App\Control\Application\Services\ControlInfrastructureService;
use Inertia\Inertia;
use Inertia\Response;

final class InfrastructureController
{
    public function __construct(
        private readonly ControlInfrastructureService $infrastructure,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Control/Infrastructure/Index', [
            'snapshot' => $this->infrastructure->snapshot(),
        ]);
    }
}
