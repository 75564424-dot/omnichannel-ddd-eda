<?php

declare(strict_types=1);

namespace App\Http\Controllers\Control;

use App\Control\Application\Services\ControlMiddlewareService;
use Inertia\Inertia;
use Inertia\Response;

final class MiddlewareGlobalController
{
    public function __construct(
        private readonly ControlMiddlewareService $middleware,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Control/Middleware/Index', [
            'snapshot' => $this->middleware->snapshot(),
        ]);
    }
}
