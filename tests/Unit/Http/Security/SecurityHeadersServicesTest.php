<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Security;

use App\Http\Application\Security\SecurityHeadersApplicator;
use App\Http\Application\Security\SecurityHeadersCspBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class SecurityHeadersServicesTest extends TestCase
{
    #[Test]
    public function csp_builder_returns_empty_when_not_configured(): void
    {
        $builder = new SecurityHeadersCspBuilder($this->createMock(Application::class));

        $this->assertSame('', $builder->build([]));
    }

    #[Test]
    public function csp_builder_expands_vite_origins_in_local_environment(): void
    {
        $app = $this->createMock(Application::class);
        $app->method('environment')->with('local')->willReturn(true);

        $builder = new SecurityHeadersCspBuilder($app);
        $csp = $builder->build([
            'content_security_policy' => "script-src 'self' 'unsafe-inline'; connect-src 'self'",
        ]);

        $this->assertStringContainsString('http://127.0.0.1:5173', $csp);
        $this->assertStringContainsString('ws://localhost:5173', $csp);
    }

    #[Test]
    public function csp_builder_keeps_production_csp_without_vite_origins(): void
    {
        $app = $this->createMock(Application::class);
        $app->method('environment')->with('local')->willReturn(false);

        $base = "script-src 'self' 'unsafe-inline'; connect-src 'self'";
        $builder = new SecurityHeadersCspBuilder($app);

        $this->assertSame($base, $builder->build(['content_security_policy' => $base]));
    }

    #[Test]
    public function applicator_applies_configured_headers_to_response(): void
    {
        $app = $this->createMock(Application::class);
        $app->method('environment')->willReturn(false);

        $applicator = new SecurityHeadersApplicator(new SecurityHeadersCspBuilder($app));
        $request = Request::create('https://example.test/dashboard', 'GET');
        $response = new Response('ok');

        $applicator->apply($request, $response, [
            'x_frame_options' => 'DENY',
            'x_content_type_options' => 'nosniff',
            'referrer_policy' => 'strict-origin-when-cross-origin',
            'content_security_policy' => "default-src 'self'",
            'strict_transport_security' => 'max-age=31536000',
        ]);

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame("default-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertSame('max-age=31536000', $response->headers->get('Strict-Transport-Security'));
    }
}
