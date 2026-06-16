<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\Support\IntegrationManagementAuthorizer;
use App\Integration\Application\UseCases\StoreIntegrationCredentialUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IntegrationCredentialController
{
    public function __construct(
        private readonly StoreIntegrationCredentialUseCase $storeCredential,
        private readonly IntegrationManagementAuthorizer $authorizer,
    ) {}

    public function store(Request $request, string $id): JsonResponse
    {
        $this->authorizer->authorizeManageIntegrations();

        $validated = $request->validate([
            'credential_type' => 'required|string|max:30',
            'value'           => 'required|string',
        ]);

        $credentialId = $this->storeCredential->execute(
            $id,
            $validated['credential_type'],
            $validated['value'],
        );

        return response()->json([
            'success'       => true,
            'credential_id' => $credentialId,
            'message'       => 'Credential stored (encrypted).',
        ], 201);
    }
}
