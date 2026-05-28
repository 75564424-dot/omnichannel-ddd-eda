<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Persistence;

use App\Integration\Domain\Repositories\WebhookRequestRepositoryInterface;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentWebhookRequestRepository implements WebhookRequestRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $tenantContext,
    ) {}

    public function recordReceived(array $data): string
    {
        $id = (string) Str::uuid();
        DB::table('webhook_requests')->insert([
            'id'              => $id,
            'tenant_id'       => $this->tenantContext->tenantId(),
            'integration_id'  => $data['integration_id'] ?? null,
            'channel_id'      => $data['channel_id'] ?? null,
            'correlation_id'  => $data['correlation_id'] ?? null,
            'http_method'     => $data['http_method'],
            'request_path'    => $data['request_path'],
            'request_headers' => json_encode($data['request_headers'] ?? [], JSON_THROW_ON_ERROR),
            'request_body'    => json_encode($data['request_body'] ?? [], JSON_THROW_ON_ERROR),
            'source_ip'       => $data['source_ip'] ?? null,
            'received_at'     => now(),
            'status'          => $data['status'] ?? 'received',
            'created_at'      => now(),
        ]);

        return $id;
    }

    public function markStatus(string $id, string $status): void
    {
        DB::table('webhook_requests')->where('id', $id)->update(['status' => $status]);
    }

    public function recordResponse(array $data): string
    {
        $id = (string) Str::uuid();
        DB::table('webhook_responses')->insert([
            'id'                => $id,
            'webhook_request_id'=> $data['webhook_request_id'],
            'http_status'       => $data['http_status'],
            'response_headers'  => json_encode($data['response_headers'] ?? [], JSON_THROW_ON_ERROR),
            'response_body'     => json_encode($data['response_body'] ?? [], JSON_THROW_ON_ERROR),
            'sent_at'           => now(),
            'latency_ms'        => $data['latency_ms'] ?? null,
            'created_at'        => now(),
        ]);

        return $id;
    }
}
