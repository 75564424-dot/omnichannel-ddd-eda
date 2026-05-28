<?php

declare(strict_types=1);

namespace Tests\Integration\Dashboard;

use App\Dashboard\Listeners\MiddlewareMetricsListener;
use App\Dashboard\Listeners\UniversalDashboardFeedListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Core dashboard feed observers must not depend on HTTP controllers or monolith-style module roots.
 */
final class DashboardFeedListenersDependencyBoundaryTest extends TestCase
{
    #[DataProvider('listener_classes')]
    #[Test]
    public function listener_constructors_exclude_inappropriate_application_layers(string $class): void
    {
        $method = new ReflectionMethod($class, '__construct');
        foreach ($method->getParameters() as $parameter) {
            $names = $this->expandTypeNames($parameter->getType());
            foreach ($names as $name) {
                $this->assertStringNotContainsString(
                    'App\\Http\\',
                    $name,
                    "{$class}::__construct must not depend on HTTP layer (found {$name})."
                );
            }
        }
    }

    /** @return list<array{string}> */
    public static function listener_classes(): array
    {
        return [
            [UniversalDashboardFeedListener::class],
            [MiddlewareMetricsListener::class],
        ];
    }

    /** @return list<string> */
    private function expandTypeNames(\ReflectionType|null $type): array
    {
        if ($type === null) {
            return [];
        }

        if ($type instanceof ReflectionNamedType) {
            return [$type->getName()];
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            $names = [];
            foreach ($type->getTypes() as $inner) {
                if ($inner instanceof ReflectionNamedType) {
                    $names[] = $inner->getName();
                }
            }

            return $names;
        }

        return [];
    }
}
