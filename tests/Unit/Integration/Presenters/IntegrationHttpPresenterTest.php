<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Presenters;

use App\Integration\Application\Presenters\IntegrationHttpPresenter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IntegrationHttpPresenterTest extends TestCase
{
    #[Test]
    public function list_envelope_preserves_count_and_data(): void
    {
        $response = (new IntegrationHttpPresenter())->list([
            ['id' => '1', 'code' => 'shopify-in'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);
        $this->assertTrue($payload['success']);
        $this->assertSame(1, $payload['count']);
        $this->assertSame('shopify-in', $payload['data'][0]['code']);
    }

    #[Test]
    public function created_and_updated_responses_match_admin_api_contract(): void
    {
        $presenter = new IntegrationHttpPresenter();

        $created = $presenter->created('int-uuid')->getData(true);
        $updated = $presenter->updated()->getData(true);
        $deleted = $presenter->deleted()->getData(true);

        $this->assertSame('int-uuid', $created['id']);
        $this->assertSame('Integration updated.', $updated['message']);
        $this->assertSame('Integration deleted.', $deleted['message']);
    }

    #[Test]
    public function not_found_returns_404_error_envelope(): void
    {
        $response = (new IntegrationHttpPresenter())->notFound('Integration missing');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'error'   => 'Integration missing',
        ], $response->getData(true));
    }
}
