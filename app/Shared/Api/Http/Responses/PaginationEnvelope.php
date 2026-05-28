<?php

declare(strict_types=1);

namespace App\Shared\Api\Http\Responses;

/**
 * Standard pagination envelope (Plan_APIs Fase 2).
 */
final class PaginationEnvelope
{
    /**
     * @param array<int, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function wrap(array $data, int $page, int $limit, int $total, bool $success = true): array
    {
        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;

        return [
            'success'    => $success,
            'data'       => $data,
            'count'      => count($data),
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public static function resolvePageLimit(?int $page, ?int $limit): array
    {
        $default = (int) config('platform_api.pagination.default_limit', 50);
        $max     = (int) config('platform_api.pagination.max_limit', 200);

        $resolvedPage  = max(1, $page ?? 1);
        $resolvedLimit = max(1, min($max, $limit ?? $default));

        return [$resolvedPage, $resolvedLimit];
    }
}
