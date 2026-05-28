<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\GetModulesCatalogUseCase;
use Illuminate\Http\JsonResponse;

final class ModulesCatalogController
{
    public function __construct(
        private readonly GetModulesCatalogUseCase $getModulesCatalog,
    ) {}

    public function catalog(): JsonResponse
    {
        return response()->json($this->getModulesCatalog->execute());
    }
}
