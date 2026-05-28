<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\TenantModuleCatalogService;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantModuleCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_loads_pruebas_retail_catalog_from_instance_files_when_settings_empty(): void
    {
        $tenant = TenantModel::query()->create([
            'id'       => '22222222-2222-2222-2222-222222222222',
            'name'     => 'Pruebas Retail',
            'slug'     => 'pruebas-retail',
            'status'   => 'active',
            'settings' => [],
        ]);

        $catalog = app(TenantModuleCatalogService::class)->getCatalog($tenant);
        $types = [];
        foreach ($catalog['producers'] ?? [] as $producer) {
            foreach ($producer['event_types_emitted'] ?? [] as $type) {
                $types[] = (string) $type;
            }
        }

        $this->assertContains('prueba01.sale.complete', $types);
        $this->assertNotContains('AcmePOS.Sale.Completed', $types);
    }
}
