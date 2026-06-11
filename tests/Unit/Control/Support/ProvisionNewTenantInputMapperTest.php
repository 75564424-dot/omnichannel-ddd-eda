<?php

declare(strict_types=1);

namespace Tests\Unit\Control\Support;

use App\Control\Application\Services\Support\ProvisionNewTenantInputMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProvisionNewTenantInputMapperTest extends TestCase
{
    #[Test]
    public function it_filters_empty_profile_fields_and_ensures_middleware_module(): void
    {
        $mapper = new ProvisionNewTenantInputMapper();

        $mapped = $mapper->map([
            'legal_name' => 'Acme Corp',
            'tax_id' => '',
            'industry' => null,
            'country' => 'ES',
            'timezone' => 'UTC',
            'modules' => ['dashboard', 'dashboard'],
        ]);

        $this->assertSame([
            'legal_name' => 'Acme Corp',
            'country' => 'ES',
            'timezone' => 'UTC',
        ], $mapped['profile']);
        $this->assertSame(['dashboard', 'middleware'], $mapped['modules']);
    }

    #[Test]
    public function it_keeps_middleware_when_already_present(): void
    {
        $mapper = new ProvisionNewTenantInputMapper();

        $mapped = $mapper->map([
            'modules' => ['middleware', 'simulation'],
        ]);

        $this->assertSame(['middleware', 'simulation'], $mapped['modules']);
    }
}
