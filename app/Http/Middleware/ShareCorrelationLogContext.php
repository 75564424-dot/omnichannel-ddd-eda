<?php

declare(strict_types=1);

namespace App\Http\Middleware;

/**
 * @deprecated Use CorrelationIdMiddleware — kept for middleware alias compatibility.
 */
final class ShareCorrelationLogContext extends \App\Http\Middleware\CorrelationIdMiddleware
{
}
