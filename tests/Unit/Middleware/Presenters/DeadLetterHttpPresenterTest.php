<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware\Presenters;

use App\Middleware\Application\Presenters\DeadLetterHttpPresenter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DeadLetterHttpPresenterTest extends TestCase
{
    #[Test]
    public function list_envelope_preserves_count_and_data(): void
    {
        $response = (new DeadLetterHttpPresenter())->list([
            ['id' => 7, 'event_id' => 'evt-1'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['success']);
        $this->assertSame(1, $payload['count']);
        $this->assertSame('evt-1', $payload['data'][0]['event_id']);
    }

    #[Test]
    public function resolve_and_requeue_messages_match_api_contract(): void
    {
        $presenter = new DeadLetterHttpPresenter();

        $resolved = $presenter->resolved(3)->getData(true);
        $requeued = $presenter->requeued(3)->getData(true);
        $notFound = $presenter->notFound('Missing dead letter')->getData(true);

        $this->assertSame('Dead letter #3 marked as resolved.', $resolved['message']);
        $this->assertSame('Dead letter #3 requeued for processing.', $requeued['message']);
        $this->assertSame(['success' => false, 'error' => 'Missing dead letter'], $notFound);
    }
}
