<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProblemDetailsApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function validation_error_returns_problem_details_on_v1_publish(): void
    {
        config()->set('platform_api.problem_details.enabled', true);

        $this->postJson('/api/v1/middleware/events/publish', [])
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonStructure(['type', 'title', 'status']);
    }

    #[Test]
    public function unauthorized_returns_problem_details_when_auth_enabled(): void
    {
        config()->set('platform_api.problem_details.enabled', true);
        config()->set('security.api_auth_enabled', true);
        config()->set('security.api_keys', []);

        $this->getJson('/api/v1/middleware/queue')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/problem+json');
    }
}
