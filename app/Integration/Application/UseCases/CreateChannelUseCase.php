<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\ChannelRepositoryInterface;

final class CreateChannelUseCase
{
    public function __construct(private readonly ChannelRepositoryInterface $channels) {}

    public function execute(array $data): string
    {
        return $this->channels->create($data);
    }
}
