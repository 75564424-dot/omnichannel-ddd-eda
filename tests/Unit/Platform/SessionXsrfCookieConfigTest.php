<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SessionXsrfCookieConfigTest extends TestCase
{
    #[Test]
    public function xsrf_cookie_name_is_derived_from_session_cookie_for_local_fleet(): void
    {
        config([
            'session.cookie'      => 'platform_session_acme_retail',
            'session.xsrf_cookie' => 'platform_xsrf_acme_retail',
        ]);

        $this->assertSame('platform_xsrf_acme_retail', config('session.xsrf_cookie'));
        $this->assertNotSame(config('session.cookie'), config('session.xsrf_cookie'));
    }
}
