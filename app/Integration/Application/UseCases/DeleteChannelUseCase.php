<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\ChannelRepositoryInterface;

final class DeleteChannelUseCase
{
    public function __construct(private readonly ChannelRepositoryInterface $channels) {}

    public function execute(string $id): void
    {
        $this->channels->delete($id);
    }
}
