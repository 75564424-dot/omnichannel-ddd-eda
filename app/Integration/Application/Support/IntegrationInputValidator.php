<?php

declare(strict_types=1);

namespace App\Integration\Application\Support;

use Illuminate\Http\Request;

final class IntegrationInputValidator
{
    /**
     * @return array<string, mixed>
     */
    public function validateStore(Request $request): array
    {
        return $request->validate([
            'code'        => 'required|string|max:60',
            'name'        => 'required|string|max:120',
            'direction'   => 'required|in:inbound,outbound,bidirectional',
            'channel_id'  => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',
            'status'      => 'sometimes|string|max:20',
            'config'      => 'sometimes|array',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name'        => 'sometimes|string|max:120',
            'direction'   => 'sometimes|in:inbound,outbound,bidirectional',
            'channel_id'  => 'nullable|uuid',
            'provider_id' => 'nullable|uuid',
            'status'      => 'sometimes|string|max:20',
            'config'      => 'sometimes|array',
        ]);
    }
}
