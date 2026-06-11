<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Ops;

use App\Console\Application\Services\Ops\PurgePlatformRetentionTableValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PurgePlatformRetentionTableValidatorTest extends TestCase
{
    #[Test]
    public function normalize_empty_string_to_null(): void
    {
        $validator = new PurgePlatformRetentionTableValidator();

        $this->assertNull($validator->normalize(''));
        $this->assertNull($validator->normalize(null));
        $this->assertSame('message_queue', $validator->normalize('message_queue'));
    }

    #[Test]
    public function is_valid_accepts_allowed_tables_and_null(): void
    {
        $validator = new PurgePlatformRetentionTableValidator();

        $this->assertTrue($validator->isValid(null));
        $this->assertTrue($validator->isValid('message_queue'));
        $this->assertFalse($validator->isValid('users'));
    }
}
