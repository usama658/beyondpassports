<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit L-3 regression: config/cors.php exposes the public XHR endpoints (apply, track/lookup,
 * documents/upload) to the front-end origin. Laravel's HandleCors middleware answers the browser
 * preflight and echoes Access-Control-Allow-Origin.
 *
 * DEV DEFAULT for UKV_FRONTEND_ORIGIN is '*', so we accept either the wildcard or the echoed
 * origin (whichever the configured allowlist resolves to) — the contract under test is that the
 * header is PRESENT on a cross-origin request to /apply.
 */
final class CorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_options_apply_returns_allow_origin_header(): void
    {
        $origin = 'https://apply.example.com';

        // Browser CORS preflight: OPTIONS with Origin + Access-Control-Request-Method.
        $response = $this->call('OPTIONS', '/apply', [], [], [], [
            'HTTP_ORIGIN' => $origin,
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $allowOrigin = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotNull(
            $allowOrigin,
            'CORS preflight on /apply must return an Access-Control-Allow-Origin header.'
        );

        // With the dev default ('*') the wildcard is returned; with an explicit allowlist the
        // matching origin is echoed. Either satisfies the contract.
        $this->assertTrue(
            $allowOrigin === '*' || $allowOrigin === $origin,
            "Allow-Origin should be '*' or the echoed origin, got: {$allowOrigin}"
        );
    }

    public function test_cross_origin_post_to_apply_carries_allow_origin_header(): void
    {
        $origin = 'https://apply.example.com';

        // A cross-origin POST (invalid body -> 422) still passes through HandleCors, which sets
        // the Allow-Origin header regardless of the controller's response status.
        $response = $this->call('POST', '/apply', [], [], [], [
            'HTTP_ORIGIN' => $origin,
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $allowOrigin = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotNull(
            $allowOrigin,
            'A cross-origin POST to /apply must carry an Access-Control-Allow-Origin header.'
        );
        $this->assertTrue($allowOrigin === '*' || $allowOrigin === $origin);
    }
}
