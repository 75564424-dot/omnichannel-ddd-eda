<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use Illuminate\Support\Facades\File;
use JsonException;
use RuntimeException;

/**
 * Loads versioned client simulation fixtures (Plan_SimulacionClientes.md).
 */
final class ClientFixtureLoader
{
    public function fixturePath(string $slug): string
    {
        return base_path('tests/fixtures/clients/'.trim($slug));
    }

    public function exists(string $slug): bool
    {
        $path = $this->fixturePath($slug);

        return is_dir($path)
            && is_readable($path.'/modules_config.json')
            && is_readable($path.'/eventbus_overlay.json')
            && is_readable($path.'/sample_events.json');
    }

    /**
     * @return array<string, mixed>
     */
    public function loadModulesConfig(string $slug): array
    {
        return $this->readJson($slug, 'modules_config.json');
    }

    /**
     * @return array{producers: array<string, mixed>, subscriptions: array<string, mixed>, consumer_registrars?: list<string>}
     */
    public function loadEventbusOverlay(string $slug): array
    {
        /** @var array<string, mixed> $overlay */
        $overlay = $this->readJson($slug, 'eventbus_overlay.json');

        return [
            'producers'           => is_array($overlay['producers'] ?? null) ? $overlay['producers'] : [],
            'subscriptions'       => is_array($overlay['subscriptions'] ?? null) ? $overlay['subscriptions'] : [],
            'consumer_registrars' => is_array($overlay['consumer_registrars'] ?? null)
                ? array_values(array_filter($overlay['consumer_registrars'], 'is_string'))
                : [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function loadSampleEvents(string $slug): array
    {
        $events = $this->readJson($slug, 'sample_events.json');
        if (! is_array($events)) {
            return [];
        }

        return array_values(array_filter($events, 'is_array'));
    }

    /**
     * @return list<string>
     */
    public function availableSlugs(): array
    {
        $root = base_path('tests/fixtures/clients');
        if (! is_dir($root)) {
            return [];
        }

        $slugs = [];
        foreach (File::directories($root) as $dir) {
            $slug = basename($dir);
            if ($this->exists($slug)) {
                $slugs[] = $slug;
            }
        }

        sort($slugs);

        return $slugs;
    }

    public function applyToFilesystem(string $slug): void
    {
        if (! $this->exists($slug)) {
            throw new RuntimeException("Client fixture not found: {$slug}");
        }

        $sourceModules = $this->fixturePath($slug).'/modules_config.json';
        $targetModules = config_path('modules/modules_config.json');
        File::copy($sourceModules, $targetModules);

        $sourceOverlay = $this->fixturePath($slug).'/eventbus_overlay.json';
        $targetOverlay = config_path('eventbus_client_overlay.json');
        File::copy($sourceOverlay, $targetOverlay);
    }

    public function applyToRuntimeConfig(string $slug): void
    {
        if (! $this->exists($slug)) {
            throw new RuntimeException("Client fixture not found: {$slug}");
        }

        $modules = $this->loadModulesConfig($slug);
        $catalog = [
            'middleware'  => is_array($modules['middleware'] ?? null) ? $modules['middleware'] : [],
            'producers'   => is_array($modules['producers'] ?? null) ? array_values($modules['producers']) : [],
            'subscribers' => is_array($modules['subscribers'] ?? null) ? array_values($modules['subscribers']) : [],
        ];

        config()->set('modules.catalog', $catalog);
        if (isset($modules['service_contact_message']) && is_string($modules['service_contact_message'])) {
            config()->set('modules.service_contact_message', $modules['service_contact_message']);
        }

        $overlay = $this->loadEventbusOverlay($slug);

        /** @var array<string, mixed> $producers */
        $producers = config('eventbus.producers', []);
        if (! is_array($producers)) {
            $producers = [];
        }

        /** @var array<string, mixed> $subscriptions */
        $subscriptions = config('eventbus.subscriptions', []);
        if (! is_array($subscriptions)) {
            $subscriptions = [];
        }

        config()->set('eventbus.producers', array_replace($producers, $overlay['producers']));
        config()->set('eventbus.subscriptions', array_replace($subscriptions, $overlay['subscriptions']));
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $slug, string $filename): array
    {
        $path = $this->fixturePath($slug).'/'.$filename;
        if (! is_readable($path)) {
            throw new RuntimeException("Missing fixture file: {$path}");
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid JSON in {$path}: {$e->getMessage()}", 0, $e);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
