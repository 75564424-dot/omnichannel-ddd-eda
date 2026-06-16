<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\Support\IntegrationManagementAuthorizer;
use App\Integration\Application\UseCases\DispatchOutboundConnectorUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class IntegrationOutboundController
{
    public function __construct(
        private readonly DispatchOutboundConnectorUseCase $dispatchOutbound,
        private readonly IntegrationManagementAuthorizer $authorizer,
    ) {}

    public function dispatch(Request $request, string $id, string $connectorId): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        /** @var array<string, mixed> $payload */
        $payload = $request->validate(['payload' => 'required|array'])['payload'];

        try {
            $result = $this->dispatchOutbound->execute($id, $connectorId, $payload);
        } catch (RuntimeException $e) {
            $code = $e->getCode();
            $status = is_int($code) && $code >= 400 ? $code : 422;

            return response()->json(['success' => false, 'error' => $e->getMessage()], $status);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }
}
