<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\ChannelRepositoryInterface;

final class UpdateChannelUseCase
{
    public function __construct(private readonly ChannelRepositoryInterface $channels) {}

    public function execute(string $id, array $data): void
    {
        $this->channels->update($id, $data);
    }
}
