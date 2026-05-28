<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class ApiPaginationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function queue_supports_page_and_limit(): void
    {
        $this->getJson('/api/v1/middleware/queue?page=1&limit=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data',
                'pagination' => ['page', 'limit', 'total', 'total_pages'],
            ])
            ->assertJsonPath('pagination.page', 1)
            ->assertJsonPath('pagination.limit', 10);
    }

    #[Test]
    public function feed_supports_page_and_limit(): void
    {
        $this->getJson('/api/v1/dashboard/events/feed?page=1&limit=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('pagination.page', 1);
    }
}
