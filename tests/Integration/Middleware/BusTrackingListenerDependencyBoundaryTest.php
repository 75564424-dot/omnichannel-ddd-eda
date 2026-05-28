<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Middleware\Listeners\BusTrackingListener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Middleware tracking listener must not depend on HTTP or foreign application services.
 */
final class BusTrackingListenerDependencyBoundaryTest extends TestCase
{
    #[Test]
    public function constructor_parameters_exclude_foreign_bounded_context_application_layers(): void
    {
        $method = new ReflectionMethod(BusTrackingListener::class, '__construct');

        foreach ($method->getParameters() as $parameter) {
            foreach ($this->expandTypeNames($parameter->getType()) as $name) {
                foreach (['App\\Http\\', 'App\\Dashboard\\'] as $prefix) {
                    $this->assertStringNotContainsString(
                        $prefix,
                        $name,
                        'BusTrackingListener must remain a passive observer (unexpected dependency: '.$name.')'
                    );
                }
            }
        }
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
