<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\ChannelRepositoryInterface;
use RuntimeException;

final class GetChannelUseCase
{
    public function __construct(private readonly ChannelRepositoryInterface $channels) {}

    public function execute(string $id): array
    {
        $channel = $this->channels->findById($id);
        if ($channel === null) {
            throw new RuntimeException('Channel not found.', 404);
        }

        return $channel;
    }
}
