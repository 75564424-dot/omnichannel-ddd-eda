<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Connectors;

use App\Integration\Domain\Contracts\OutboundConnectorInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * HTTP outbound connector template using Laravel HTTP client (Plan_Integraciones Fase 3).
 */
final class HttpOutboundConnector implements OutboundConnectorInterface
{
    public function dispatch(string $endpoint, array $payload, array $config = [], ?string $bearerToken = null): array
    {
        if ($endpoint === '') {
            throw new RuntimeException('Outbound connector endpoint is empty.');
        }

        $timeout = (int) ($config['timeout_seconds'] ?? 30);
        $method  = strtoupper((string) ($config['method'] ?? 'POST'));

        $request = Http::timeout($timeout)->acceptJson();
        if ($bearerToken !== null && $bearerToken !== '') {
            $request = $request->withToken($bearerToken);
        }

        $response = match ($method) {
            'PUT'    => $request->put($endpoint, $payload),
            'PATCH'  => $request->patch($endpoint, $payload),
            default  => $request->post($endpoint, $payload),
        };

        $body = $response->json();
        if (! is_array($body)) {
            $body = ['raw' => $response->body()];
        }

        return [
            'status' => $response->status(),
            'body'   => $body,
        ];
    }
}
