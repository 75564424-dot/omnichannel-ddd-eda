<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Platform\Services\InertiaSharedPropsResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(
        private readonly InertiaSharedPropsResolver $sharedProps,
    ) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), $this->sharedProps->resolve($request));
    }
}
