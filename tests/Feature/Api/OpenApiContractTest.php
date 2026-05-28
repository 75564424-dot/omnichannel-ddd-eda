<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OpenApiContractTest extends TestCase
{
    #[Test]
    public function openapi_yaml_is_valid_and_contains_v1_publish_path(): void
    {
        $path = base_path('docs/api/openapi.yaml');
        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertNotFalse($contents);

        $this->assertStringContainsString('openapi: 3.0.3', $contents);
        $this->assertStringContainsString('/api/v1/middleware/events/publish:', $contents);
        $this->assertStringContainsString('PublishEventRequest:', $contents);
    }

    #[Test]
    public function postman_collection_references_v1_base_paths(): void
    {
        $json = file_get_contents(base_path('docs/api/postman_collection.json'));
        $this->assertNotFalse($json);
        $this->assertStringContainsString('/api/v1/middleware/events/publish', $json);
    }
}
