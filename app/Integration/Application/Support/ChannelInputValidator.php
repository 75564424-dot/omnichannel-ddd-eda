<?php

declare(strict_types=1);

namespace App\Integration\Application\Support;

use Illuminate\Http\Request;

final class ChannelInputValidator
{
    /**
     * @return array<string, mixed>
     */
    public function validateStore(Request $request): array
    {
        return $request->validate([
            'code'         => 'required|string|max:60',
            'name'         => 'required|string|max:120',
            'channel_type' => 'required|string|max:30',
            'status'       => 'sometimes|string|max:20',
            'metadata'     => 'sometimes|array',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name'         => 'sometimes|string|max:120',
            'channel_type' => 'sometimes|string|max:30',
            'status'       => 'sometimes|string|max:20',
            'metadata'     => 'sometimes|array',
        ]);
    }
}
