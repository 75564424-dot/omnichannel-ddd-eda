<?php

declare(strict_types=1);

namespace App\Shared\Logging;

/**
 * @deprecated Use CorrelationIdMiddleware — kept for middleware alias compatibility.
 */
final class ShareCorrelationLogContext extends \App\Http\Middleware\CorrelationIdMiddleware
{
}
