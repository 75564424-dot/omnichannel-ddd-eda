<?php

declare(strict_types=1);

namespace App\Integration\Application\Presenters;

use Illuminate\Http\JsonResponse;

final class WebhookIngressHttpPresenter
{
    /**
     * @param array{webhook_request_id: string, event_id: string, entry_id: int} $result
     */
    public function accepted(array $result): JsonResponse
    {
        return response()->json([
            'success'            => true,
            'event_id'           => $result['event_id'],
            'entry_id'           => $result['entry_id'],
            'webhook_request_id' => $result['webhook_request_id'],
        ], 202);
    }

    public function error(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $message], $status);
    }
}
