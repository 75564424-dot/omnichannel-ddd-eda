<?php

declare(strict_types=1);

namespace App\Shared\Api\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * RFC 7807 Problem Details for HTTP APIs (Plan_APIs).
 */
final class ProblemDetailsFactory
{
    public static function make(
        string $title,
        int $status,
        ?string $detail = null,
        ?string $type = null,
        ?array $extensions = null,
    ): JsonResponse {
        $typeBase = rtrim((string) config('platform_api.problem_details.type_base', 'https://api.platform.local/problems'), '/');
        $slug     = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title) ?? 'error');

        $body = array_filter([
            'type'     => $type ?? "{$typeBase}/{$slug}",
            'title'    => $title,
            'status'   => $status,
            'detail'   => $detail,
            'instance' => request()->path(),
        ], fn ($v) => $v !== null && $v !== '');

        if ($extensions !== null) {
            $body = array_merge($body, $extensions);
        }

        return response()->json($body, $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }

    public static function validation(string $detail, ?array $errors = null): JsonResponse
    {
        return self::make(
            title: 'Validation Error',
            status: 422,
            detail: $detail,
            type: null,
            extensions: $errors !== null ? ['errors' => $errors] : null,
        );
    }

    public static function unauthorized(?string $detail = null): JsonResponse
    {
        return self::make('Unauthorized', 401, $detail ?? 'Authentication required.');
    }

    public static function forbidden(?string $detail = null): JsonResponse
    {
        return self::make('Forbidden', 403, $detail ?? 'Insufficient permissions.');
    }
}
