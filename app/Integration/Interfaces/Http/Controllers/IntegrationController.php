<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\UseCases\CreateIntegrationUseCase;
use App\Integration\Application\UseCases\DeleteIntegrationUseCase;
use App\Integration\Application\UseCases\GetIntegrationUseCase;
use App\Integration\Application\UseCases\ListIntegrationsUseCase;
use App\Integration\Application\UseCases\UpdateIntegrationUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

final class IntegrationController
{
    public function __construct(
        private readonly ListIntegrationsUseCase $listIntegrations,
        private readonly GetIntegrationUseCase $getIntegration,
        private readonly CreateIntegrationUseCase $createIntegration,
        private readonly UpdateIntegrationUseCase $updateIntegration,
        private readonly DeleteIntegrationUseCase $deleteIntegration,
    ) {}

    public function index(): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $items = $this->listIntegrations->execute();

        return response()->json(['success' => true, 'data' => $items, 'count' => count($items)]);
    }

    public function show(string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        try {
            return response()->json(['success' => true, 'data' => $this->getIntegration->execute($id)]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $validated = $request->validate([
            'code'        => 'required|string|max:60',
            'name'        => 'required|string|max:120',
            'direction'   => 'required|in:inbound,outbound,bidirectional',
            'channel_id'  => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',
            'status'      => 'sometimes|string|max:20',
            'config'      => 'sometimes|array',
        ]);

        $id = $this->createIntegration->execute($validated);

        return response()->json(['success' => true, 'id' => $id], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:120',
            'direction'   => 'sometimes|in:inbound,outbound,bidirectional',
            'channel_id'  => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',
            'status'      => 'sometimes|string|max:20',
            'config'      => 'sometimes|array',
        ]);

        $this->updateIntegration->execute($id, $validated);

        return response()->json(['success' => true, 'message' => 'Integration updated.']);
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $this->deleteIntegration->execute($id);

        return response()->json(['success' => true, 'message' => 'Integration deleted.']);
    }
}
