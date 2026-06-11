<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use Illuminate\Support\Str;

/**
 * Persists client silo metadata for local multi-port development.
 */
final class LocalFleetRegistry
{
    public function __construct(
        private readonly string $registryPath,
        private readonly int $portRangeStart,
    ) {}

    /** @return list<array<string, mixed>> */
    public function clientInstances(): array
    {
        return $this->read()['instances'] ?? [];
    }

    public function findBySlug(string $slug): ?array
    {
        $slug = Str::slug($slug);

        foreach ($this->clientInstances() as $row) {
            if (Str::slug((string) ($row['slug'] ?? '')) === $slug) {
                return $row;
            }
        }

        return null;
    }

    public function isProvisioned(string $slug): bool
    {
        return $this->findBySlug($slug) !== null;
    }

    /**
     * @param array<string, mixed> $instance
     */
    public function upsert(array $instance): array
    {
        $slug = Str::slug((string) ($instance['slug'] ?? ''));
        if ($slug === '') {
            throw new \InvalidArgumentException('Instance slug is required.');
        }

        $instance['id'] = (string) ($instance['id'] ?? 'client-'.$slug);
        $instance['slug'] = $slug;
        $instance['role'] = 'client';
        $instance['port'] = (int) ($instance['port'] ?? $this->nextAvailablePort());

        $data = $this->read();
        $rows = $data['instances'] ?? [];
        $found = false;

        foreach ($rows as $index => $row) {
            if (Str::slug((string) ($row['slug'] ?? '')) === $slug) {
                $rows[$index] = array_merge($row, $instance);
                $found = true;
                break;
            }
        }

        if (! $found) {
            $rows[] = $instance;
        }

        usort($rows, static fn (array $a, array $b): int => ((int) ($a['port'] ?? 0)) <=> ((int) ($b['port'] ?? 0)));

        $data['instances'] = array_values($rows);
        $data['updated_at'] = now()->toIso8601String();
        $this->write($data);

        return $this->findBySlug($slug) ?? $instance;
    }

    /** @param list<array<string, mixed>> $instances */
    public function replaceInstances(array $instances): void
    {
        $data = $this->read();
        $data['instances'] = array_values($instances);
        $data['updated_at'] = now()->toIso8601String();
        $this->write($data);
    }

    public function nextAvailablePort(): int
    {
        $used = array_map(
            static fn (array $row): int => (int) ($row['port'] ?? 0),
            $this->clientInstances(),
        );

        $port = $this->portRangeStart;
        while (in_array($port, $used, true)) {
            ++$port;
        }

        return $port;
    }

    /** @return array<string, mixed> */
    private function read(): array
    {
        if (! is_file($this->registryPath)) {
            return ['instances' => []];
        }

        $decoded = json_decode((string) file_get_contents($this->registryPath), true);

        return is_array($decoded) ? $decoded : ['instances' => []];
    }

    /** @param array<string, mixed> $data */
    private function write(array $data): void
    {
        $directory = dirname($this->registryPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
            $this->registryPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n",
        );
    }
}
