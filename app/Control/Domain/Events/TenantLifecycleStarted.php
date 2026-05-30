<?php

declare(strict_types=1);

namespace App\Control\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantLifecycleStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $lifecycle,
        public readonly ?string $appUrl,
        public readonly int $timestamp
    ) {}
}
