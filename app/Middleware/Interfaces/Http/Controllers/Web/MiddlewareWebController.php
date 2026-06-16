<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers\Web;

use App\Middleware\Application\Services\MiddlewareIndexPageService;
use Inertia\Inertia;
use Inertia\Response;

final class MiddlewareWebController
{
    public function __construct(
        private readonly MiddlewareIndexPageService $indexPage,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Middleware/Index', $this->indexPage->buildProps());
    }
}
