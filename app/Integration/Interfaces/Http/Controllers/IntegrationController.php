<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\Presenters\IntegrationHttpPresenter;
use App\Integration\Application\Support\IntegrationInputValidator;
use App\Integration\Application\Support\IntegrationManagementAuthorizer;
use App\Integration\Application\UseCases\CreateIntegrationUseCase;
use App\Integration\Application\UseCases\DeleteIntegrationUseCase;
use App\Integration\Application\UseCases\GetIntegrationUseCase;
use App\Integration\Application\UseCases\ListIntegrationsUseCase;
use App\Integration\Application\UseCases\UpdateIntegrationUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class IntegrationController
{
    public function __construct(
        private readonly ListIntegrationsUseCase $listIntegrations,
        private readonly GetIntegrationUseCase $getIntegration,
        private readonly CreateIntegrationUseCase $createIntegration,
        private readonly UpdateIntegrationUseCase $updateIntegration,
        private readonly DeleteIntegrationUseCase $deleteIntegration,
        private readonly IntegrationManagementAuthorizer $authorizer,
        private readonly IntegrationHttpPresenter $presenter,
        private readonly IntegrationInputValidator $validator,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        return $this->presenter->list($this->listIntegrations->execute());
    }

    public function show(string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        try {
            return $this->presenter->show($this->getIntegration->execute($id));
        } catch (RuntimeException $e) {
            return $this->presenter->notFound($e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $id = $this->createIntegration->execute($this->validator->validateStore($request));

        return $this->presenter->created($id);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $this->updateIntegration->execute($id, $this->validator->validateUpdate($request));

        return $this->presenter->updated();
    }

    public function destroy(string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $this->deleteIntegration->execute($id);

        return $this->presenter->deleted();
    }
}
