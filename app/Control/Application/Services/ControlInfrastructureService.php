<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Database\DatabaseManager;
use Throwable;

final class ControlInfrastructureService
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly RedisFactory $redis,
    ) {}
    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return [
            'components' => [
                $this->component('PostgreSQL / SQLite', 'database', $this->databaseStatus()),
                $this->component('Redis', 'redis', $this->redisStatus()),
                $this->component('Event bus driver', 'hub', $this->busDriverLabel(), (string) config('eventbus.driver', 'laravel')),
                $this->component('Queue backend', 'queue', strtoupper((string) config('queue.default', 'sync'))),
                $this->component('Cache backend', 'memory', strtoupper((string) config('cache.default', 'database'))),
                $this->component('Kafka', 'kafka', $this->kafkaStatus()),
                $this->component('Kubernetes', 'k8s', $this->kubernetesStatus()),
                $this->component('API gateway', 'api', config('app.url')),
            ],
            'deployment' => [
                'mode'        => (string) config('platform.deployment_mode'),
                'client_slug' => (string) config('platform.client_slug'),
                'app_env'     => (string) config('app.env'),
            ],
        ];
    }

    /** @return array<string, string> */
    private function component(string $label, string $icon, string $status, ?string $note = null): array
    {
        return [
            'label'  => $label,
            'icon'   => $icon,
            'status' => $status,
            'note'   => $note,
        ];
    }

    private function databaseStatus(): string
    {
        try {
            $this->db->connection()->getPdo();

            return 'ok';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function redisStatus(): string
    {
        if (! $this->redisInUse()) {
            return 'not_configured';
        }

        try {
            $pong = $this->redis->connection()->ping();

            return ($pong === true || $pong === 'PONG' || $pong === '+PONG') ? 'ok' : 'fail';
        } catch (Throwable) {
            return 'fail';
        }
    }

    private function redisInUse(): bool
    {
        $drivers = [
            (string) config('cache.default'),
            (string) config('queue.default'),
            (string) config('session.driver'),
        ];

        return in_array('redis', $drivers, true);
    }

    private function busDriverLabel(): string
    {
        $driver = (string) config('eventbus.driver', 'laravel');

        return strtoupper($driver) === 'KAFKA' ? 'kafka' : 'laravel_internal';
    }

    private function kafkaStatus(): string
    {
        $brokers = (string) config('eventbus.kafka.brokers', env('EVENTBUS_KAFKA_BROKERS', ''));

        return $brokers !== '' ? 'configured' : 'not_configured';
    }

    private function kubernetesStatus(): string
    {
        if (env('KUBERNETES_SERVICE_HOST')) {
            return 'detected';
        }

        if (env('DOCKER_APP_ROLE')) {
            return 'docker_compose';
        }

        return 'local';
    }
}
