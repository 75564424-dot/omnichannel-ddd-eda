<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Control\Application\Services\ClientDashboardModulesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientDashboardModulesWebController
{
    public function __construct(
        private readonly ClientDashboardModulesService $modules,
    ) {}

    public function updateVisibility(Request $request): JsonResponse
    {
        $data = $request->validate([
            'producers'   => ['sometimes', 'array'],
            'producers.*' => ['string', 'max:120'],
            'subscribers' => ['sometimes', 'array'],
            'subscribers.*' => ['string', 'max:120'],
        ]);

        try {
            $this->modules->updateVisibleModules(
                array_values($data['producers'] ?? []),
                array_values($data['subscribers'] ?? []),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Módulos visibles actualizados.',
            'catalog' => $this->modules->presentationCatalog(),
        ]);
    }
}
