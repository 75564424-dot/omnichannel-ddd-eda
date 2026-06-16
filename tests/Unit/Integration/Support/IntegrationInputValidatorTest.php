<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Support;

use App\Integration\Application\Support\IntegrationInputValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IntegrationInputValidatorTest extends TestCase
{
    #[Test]
    public function validate_store_requires_code_name_and_direction(): void
    {
        $validated = (new IntegrationInputValidator())->validateStore(Request::create('/', 'POST', [
            'code'      => 'shopify-in',
            'name'      => 'Shopify Inbound',
            'direction' => 'inbound',
        ]));

        $this->assertSame('shopify-in', $validated['code']);
        $this->assertSame('inbound', $validated['direction']);
    }

    #[Test]
    public function validate_store_rejects_invalid_direction(): void
    {
        $this->expectException(ValidationException::class);

        (new IntegrationInputValidator())->validateStore(Request::create('/', 'POST', [
            'code'      => 'shopify-in',
            'name'      => 'Shopify Inbound',
            'direction' => 'sideways',
        ]));
    }
}
