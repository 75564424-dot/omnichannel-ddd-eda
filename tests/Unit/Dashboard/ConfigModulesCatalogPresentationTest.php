<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Infrastructure\Modules\ConfigModulesCatalogDataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Normalización defensiva del catálogo declarativo (config/modules.php ↔ JSON)
 * para la capa de presentación del dashboard — coherencia configuración ↔ visualización.
 */
final class ConfigModulesCatalogPresentationTest extends TestCase
{
    #[Test]
    public function presentation_catalog_skips_invalid_rows_and_deduplicates_event_type_lists(): void
    {
        config()->set('modules.catalog', [
            'middleware' => [
                'id'          => '',
                'name'        => '',
                'description' => 'Central bus',
                'role'        => '',
            ],
            'producers' => [
                ['id' => '', 'name' => 'No id', 'event_types_emitted' => ['A']],
                ['id' => 'p1', 'name' => '', 'event_types_emitted' => ['A']],
                [
                    'id'                  => 'retail_api',
                    'name'                => 'Retail API',
                    'event_types_emitted' => ['  Order.Placed  ', 'Order.Placed', '', 'Stock.Low'],
                ],
            ],
            'subscribers' => [
                ['id' => 's_bad', 'name' => '', 'event_types_consumed' => ['Order.Placed']],
                [
                    'id'                     => 'analytics',
                    'name'                   => 'Analytics',
                    'event_types_consumed'   => ['Order.Placed', 'Order.Placed', ''],
                ],
            ],
        ]);
        config()->set('modules.service_contact_message', 'Custom vendor message.');

        $provider = new ConfigModulesCatalogDataProvider();
        $out = $provider->getPresentationCatalog();

        $this->assertSame('middleware', $out['middleware']['id']);
        $this->assertSame('Middleware bus', $out['middleware']['name']);
        $this->assertSame('routing', $out['middleware']['role']);
        $this->assertSame('Central bus', $out['middleware']['description']);

        $this->assertCount(1, $out['producers']);
        $this->assertSame('retail_api', $out['producers'][0]['id']);
        $this->assertSame(['Order.Placed', 'Stock.Low'], $out['producers'][0]['event_types_emitted']);

        $this->assertCount(1, $out['subscribers']);
        $this->assertSame('analytics', $out['subscribers'][0]['id']);
        $this->assertSame(['Order.Placed'], $out['subscribers'][0]['event_types_consumed']);

        $this->assertSame('Custom vendor message.', $out['service_contact_message']);
    }

    #[Test]
    public function presentation_catalog_uses_default_contact_message_when_empty(): void
    {
        config()->set('modules.catalog', [
            'middleware'  => ['id' => 'mw', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
            'producers'   => [['id' => 'p', 'name' => 'P', 'event_types_emitted' => ['E']]],
            'subscribers' => [],
        ]);
        config()->set('modules.service_contact_message', '');

        $out = (new ConfigModulesCatalogDataProvider())->getPresentationCatalog();

        $this->assertStringContainsString('proveedor del servicio', $out['service_contact_message']);
    }
}
