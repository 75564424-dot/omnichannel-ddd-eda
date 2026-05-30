<?php

declare(strict_types=1);

namespace App\Control\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TenantLifecycleRestored
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $tenantId,
        public readonly string $status,
        public readonly string $lifecycle,
        public readonly int $timestamp
    ) {}
}
