<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Progress;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * Client-silo worker updates simulation run state on the control-plane host (local fleet dev).
 */
final class SimulationRunControlPlaneClient
{
    private const MAX_ATTEMPTS = 6;

    private const RETRY_SLEEP_MS = 400;

    public function __construct(
        private readonly HttpFactory $http,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function fetchRun(string $runId): array
    {
        $response = $this->request('GET', "simulation-runs/{$runId}");

        if (! $response->successful()) {
            throw new RuntimeException(
                'No se pudo cargar la simulación desde control plane: HTTP '.$response->status(),
            );
        }

        $body = $response->json();
        $data = is_array($body) ? ($body['data'] ?? $body) : null;

        if (! is_array($data)) {
            throw new RuntimeException('Respuesta inválida del control plane para la simulación.');
        }

        return $data;
    }

    public function reportProgress(string $runId, int $current, int $total): bool
    {
        try {
            $response = $this->request('PATCH', "simulation-runs/{$runId}/progress", [
                'progress_current' => $current,
                'published'        => $current,
                'planned_total'    => $total,
            ], maxAttempts: 2, timeoutSeconds: 3);

            if (! $response->successful()) {
                $this->logger->warning('simulation.progress_failed', [
                    'run_id' => $runId,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (ConnectionException $e) {
            $this->logger->warning('simulation.progress_unreachable', [
                'run_id'  => $runId,
                'message' => $e->getMessage(),
            ]);

            return false;
        } catch (Throwable $e) {
            $this->logger->warning('simulation.progress_error', [
                'run_id'  => $runId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function markCompleted(string $runId, array $payload): void
    {
        try {
            $response = $this->request('POST', "simulation-runs/{$runId}/complete", $payload, maxAttempts: 2, timeoutSeconds: 5);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'No se pudo marcar simulación completada: HTTP '.$response->status().' '.$response->body(),
                );
            }
        } catch (ConnectionException $e) {
            $this->logger->warning('simulation.complete_unreachable', [
                'run_id'  => $runId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function markFailed(string $runId, string $message, array $context = []): void
    {
        try {
            $response = $this->request('POST', "simulation-runs/{$runId}/fail", [
                'error_message' => $message,
                'context'       => $context,
            ]);

            if ($response->successful() || $response->status() === 404) {
                return;
            }

            throw new RuntimeException(
                'No se pudo marcar simulación fallida: HTTP '.$response->status().' '.$response->body(),
            );
        } catch (ConnectionException $e) {
            $this->logger->warning('simulation.fail_unreachable', [
                'run_id'  => $runId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function request(
        string $method,
        string $path,
        ?array $body = null,
        int $maxAttempts = self::MAX_ATTEMPTS,
        int $timeoutSeconds = 8,
    ): \Illuminate\Http\Client\Response {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $pending = $this->client($timeoutSeconds);
                $response = match (strtoupper($method)) {
                    'GET'   => $pending->get($this->url($path)),
                    'PATCH' => $pending->patch($this->url($path), $body ?? []),
                    'POST'  => $pending->post($this->url($path), $body ?? []),
                    default => throw new RuntimeException("Unsupported HTTP method: {$method}"),
                };

                return $response;
            } catch (ConnectionException $e) {
                $lastException = $e;
                if ($attempt < $maxAttempts) {
                    usleep(self::RETRY_SLEEP_MS * 1000);
                }
            }
        }

        throw $lastException ?? new RuntimeException('Control plane unreachable.');
    }

    private function client(int $timeoutSeconds = 8): \Illuminate\Http\Client\PendingRequest
    {
        return $this->http->connectTimeout(2)
            ->timeout($timeoutSeconds)
            ->acceptJson()
            ->withHeaders([
                'X-Simulation-Internal-Token' => (string) config('platform.simulation.internal_token', ''),
            ]);
    }

    private function url(string $path): string
    {
        $base = rtrim((string) config('platform.simulation.control_plane_url', ''), '/');
        if ($base === '') {
            throw new RuntimeException('PLATFORM_CONTROL_PLANE_URL no está configurado en el silo cliente.');
        }

        return $base.'/control/internal/'.$path;
    }
}
