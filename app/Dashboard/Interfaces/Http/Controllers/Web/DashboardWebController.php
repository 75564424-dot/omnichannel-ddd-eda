<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers\Web;

use App\Dashboard\Application\Services\DashboardIndexPageService;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardWebController
{
    public function __construct(
        private readonly DashboardIndexPageService $indexPage,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', $this->indexPage->buildProps());
    }
}
