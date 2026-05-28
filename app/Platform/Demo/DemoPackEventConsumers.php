<?php

declare(strict_types=1);

namespace App\Platform\Demo;

use App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface;

/**
 * Ejemplo de registro declarativo de un pack (Fase C).
 * Para probar en local: añadir esta clase a `config('eventbus.consumer_registrars')`.
 */
final class DemoPackEventConsumers implements EventConsumerRegistrationInterface
{
    public static function subscriptionCatalog(): array
    {
        return [
            'Platform.Demo.Pack' => [
                [
                    'module'   => 'DemoPack',
                    'listener' => DemoPackListener::class,
                    'queue'    => 'sync',
                ],
            ],
        ];
    }
}
