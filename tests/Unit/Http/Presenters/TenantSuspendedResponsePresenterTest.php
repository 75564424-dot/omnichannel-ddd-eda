<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Presenters;

use App\Http\Application\Presenters\TenantSuspendedResponsePresenter;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantSuspendedResponsePresenterTest extends TestCase
{
    #[Test]
    public function api_request_returns_problem_details_payload(): void
    {
        $request = Request::create('/api/middleware/status', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = (new TenantSuspendedResponsePresenter())->respond($request);

        $this->assertSame(403, $response->getStatusCode());
        $payload = json_decode((string) $response->getContent(), true);
        $this->assertSame('tenant_suspended', $payload['type']);
        $this->assertStringContainsString('suspendido', (string) $payload['detail']);
    }
}
