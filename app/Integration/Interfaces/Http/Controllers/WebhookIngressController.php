<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Http\Controllers;

use App\Integration\Application\Presenters\WebhookIngressHttpPresenter;
use App\Integration\Application\Services\WebhookRequestHeadersNormalizer;
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
        private readonly WebhookRequestHeadersNormalizer $headersNormalizer,
        private readonly WebhookIngressHttpPresenter $presenter,
    ) {}

    /**
     * POST /api/integrations/webhooks/{integrationCode}
     */
    public function receive(Request $request, string $integrationCode): JsonResponse
    {
        $rawBody = $request->getContent();
        /** @var array<string, mixed> $body */
        $body = $request->json()->all();

        try {
            $result = $this->receiveWebhook->execute(
                integrationCode: $integrationCode,
                rawBody: $rawBody,
                body: $body,
                headers: $this->headersNormalizer->fromRequest($request),
                httpMethod: $request->method(),
                requestPath: $request->path(),
                sourceIp: $request->ip(),
            );
        } catch (RuntimeException $e) {
            $code = $e->getCode();
            $status = is_int($code) && $code >= 400 && $code < 600 ? $code : 422;

            return $this->presenter->error($e->getMessage(), $status);
        } catch (\Throwable $e) {
            return $this->presenter->error($e->getMessage(), 422);
        }

        return $this->presenter->accepted($result);
    }
}
