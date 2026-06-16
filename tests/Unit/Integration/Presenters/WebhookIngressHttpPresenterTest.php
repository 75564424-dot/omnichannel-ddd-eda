<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Presenters;

use App\Integration\Application\Presenters\WebhookIngressHttpPresenter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebhookIngressHttpPresenterTest extends TestCase
{
    #[Test]
    public function accepted_response_matches_webhook_ingress_contract(): void
    {
        $response = (new WebhookIngressHttpPresenter())->accepted([
            'webhook_request_id' => 'wh-1',
            'event_id'           => 'evt-1',
            'entry_id'           => 42,
        ]);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame([
            'success'            => true,
            'event_id'           => 'evt-1',
            'entry_id'           => 42,
            'webhook_request_id' => 'wh-1',
        ], $response->getData(true));
    }

    #[Test]
    public function error_response_preserves_status_code(): void
    {
        $response = (new WebhookIngressHttpPresenter())->error('Invalid signature', 401);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'error'   => 'Invalid signature',
        ], $response->getData(true));
    }
}
