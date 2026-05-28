<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\UseCases\ReceiveWebhookUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Inbound webhook ingress — HMAC verified, no platform API key required.
 */
final class WebhookIngressController
{
    public function __construct(
        private readonly ReceiveWebhookUseCase $receiveWebhook,
    ) {}

    /**
     * POST /api/integrations/webhooks/{integrationCode}
     */
    public function receive(Request $request, string $integrationCode): JsonResponse
    {
        $rawBody = $request->getContent();
        /** @var array<string, mixed> $body */
        $body = $request->json()->all();

        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            $headers[strtolower((string) $key)] = is_array($values) ? ($values[0] ?? null) : $values;
        }

        try {
            $result = $this->receiveWebhook->execute(
                integrationCode: $integrationCode,
                rawBody: $rawBody,
                body: $body,
                headers: $headers,
                httpMethod: $request->method(),
                requestPath: $request->path(),
                sourceIp: $request->ip(),
            );
        } catch (RuntimeException $e) {
            $code = $e->getCode();
            $status = is_int($code) && $code >= 400 && $code < 600 ? $code : 422;

            return response()->json(['success' => false, 'error' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success'            => true,
            'event_id'           => $result['event_id'],
            'entry_id'           => $result['entry_id'],
            'webhook_request_id' => $result['webhook_request_id'],
        ], 202);
    }
}
