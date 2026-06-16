<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use Illuminate\Http\Request;

final class WebhookRequestHeadersNormalizer
{
    /**
     * @return array<string, string|null>
     */
    public function fromRequest(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            $headers[strtolower((string) $key)] = is_array($values) ? ($values[0] ?? null) : $values;
        }

        return $headers;
    }
}
