<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use InvalidArgumentException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;

/**
 * Optional JSON Schema validation for publish payloads (Plan_Seguridad / Plan_Middleware).
 */
final class PublishPayloadSchemaValidator
{
    public function __construct(
        private readonly EventSchemaRegistry $schemaRegistry,
    ) {}

    public function validate(string $eventType, array $payload): void
    {
        if (! config('eventbus.schema_validation_enabled', false)) {
            return;
        }

        $resolved = $this->schemaRegistry->resolve($eventType);
        if ($resolved === null) {
            return;
        }

        $schemaPath = $resolved['path'];
        if (! is_readable($schemaPath)) {
            throw new InvalidArgumentException("JSON Schema file not readable for event type '{$eventType}'.");
        }

        $schemaJson = file_get_contents($schemaPath);
        if ($schemaJson === false) {
            throw new InvalidArgumentException("Unable to read JSON Schema for event type '{$eventType}'.");
        }

        $schema = json_decode($schemaJson, false);
        if ($schema === null) {
            throw new InvalidArgumentException("Invalid JSON Schema file for event type '{$eventType}'.");
        }

        $validator = new Validator;
        $result = $validator->validate(json_decode(json_encode($payload, JSON_THROW_ON_ERROR), false), $schema);

        if ($result->isValid()) {
            return;
        }

        $formatter = new ErrorFormatter;
        $errors = $formatter->format($result->error());

        throw new InvalidArgumentException(
            'EventBus schema validation failed: '.json_encode($errors, JSON_THROW_ON_ERROR)
        );
    }
}
