<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\Presenters\ChannelHttpPresenter;
use App\Integration\Application\Support\ChannelInputValidator;
use App\Integration\Application\Support\IntegrationManagementAuthorizer;
use App\Integration\Application\UseCases\CreateChannelUseCase;
use App\Integration\Application\UseCases\DeleteChannelUseCase;
use App\Integration\Application\UseCases\GetChannelUseCase;
use App\Integration\Application\UseCases\ListChannelsUseCase;
use App\Integration\Application\UseCases\UpdateChannelUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ChannelController
{
    public function __construct(
        private readonly ListChannelsUseCase $listChannels,
        private readonly GetChannelUseCase $getChannel,
        private readonly CreateChannelUseCase $createChannel,
        private readonly UpdateChannelUseCase $updateChannel,
        private readonly DeleteChannelUseCase $deleteChannel,
        private readonly IntegrationManagementAuthorizer $authorizer,
        private readonly ChannelHttpPresenter $presenter,
        private readonly ChannelInputValidator $validator,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        return $this->presenter->list($this->listChannels->execute());
    }

    public function show(string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        try {
            return $this->presenter->show($this->getChannel->execute($id));
        } catch (RuntimeException $e) {
            return $this->presenter->notFound($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $id = $this->createChannel->execute($this->validator->validateStore($request));

        return $this->presenter->created($id);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $this->updateChannel->execute($id, $this->validator->validateUpdate($request));

        return $this->presenter->updated();
    }

    public function destroy(string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $this->deleteChannel->execute($id);

        return $this->presenter->deleted();
    }
}
