<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\UseCases\CreateChannelUseCase;
use App\Integration\Application\UseCases\DeleteChannelUseCase;
use App\Integration\Application\UseCases\GetChannelUseCase;
use App\Integration\Application\UseCases\ListChannelsUseCase;
use App\Integration\Application\UseCases\UpdateChannelUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

final class ChannelController
{
    public function __construct(
        private readonly ListChannelsUseCase $listChannels,
        private readonly GetChannelUseCase $getChannel,
        private readonly CreateChannelUseCase $createChannel,
        private readonly UpdateChannelUseCase $updateChannel,
        private readonly DeleteChannelUseCase $deleteChannel,
    ) {}

    public function index(): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $items = $this->listChannels->execute();

        return response()->json(['success' => true, 'data' => $items, 'count' => count($items)]);
    }

    public function show(string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        try {
            return response()->json(['success' => true, 'data' => $this->getChannel->execute($id)]);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $validated = $request->validate([
            'code'         => 'required|string|max:60',
            'name'         => 'required|string|max:120',
            'channel_type' => 'required|string|max:30',
            'status'       => 'sometimes|string|max:20',
            'metadata'     => 'sometimes|array',
        ]);

        $id = $this->createChannel->execute($validated);

        return response()->json(['success' => true, 'id' => $id], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:120',
            'channel_type' => 'sometimes|string|max:30',
            'status'       => 'sometimes|string|max:20',
            'metadata'     => 'sometimes|array',
        ]);

        $this->updateChannel->execute($id, $validated);

        return response()->json(['success' => true, 'message' => 'Channel updated.']);
    }

    public function destroy(string $id): JsonResponse
    {
        Gate::authorize('platform.manage-integrations');

        $this->deleteChannel->execute($id);

        return response()->json(['success' => true, 'message' => 'Channel deleted.']);
    }
}
