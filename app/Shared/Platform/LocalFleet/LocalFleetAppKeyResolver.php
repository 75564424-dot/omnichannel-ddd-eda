<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

final class LocalFleetAppKeyResolver
{
    public function resolve(string $envId): string
    {
        $envPath = base_path('.env.'.$envId);
        if (is_file($envPath)) {
            $contents = (string) file_get_contents($envPath);
            if (preg_match('/^APP_KEY=(.+)$/m', $contents, $matches) === 1) {
                $key = trim($matches[1]);
                if ($key !== '') {
                    return $key;
                }
            }
        }

        return 'base64:'.base64_encode(random_bytes(32));
    }
}
