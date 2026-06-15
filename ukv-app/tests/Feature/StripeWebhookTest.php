<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stripe webhook endpoint (POST /stripe/webhook -> StripeWebhookController).
 *
 * We do NOT exercise a real Stripe event. The contract under test is:
 *   - the route exists and is CSRF-exempt (a tokenless POST must NOT 419);
 *   - a bogus body / invalid signature is rejected with 400 (SignatureVerificationException),
 *     never a 500 and never a 419.
 *
 * Signature verification needs a webhook secret; we set a dummy one so constructEvent runs
 * its verification path (and fails on the bogus signature) rather than erroring on a null
 * secret. The stripe-php SDK is installed, so the controller's 400 branch is reachable.
 */
final class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A non-empty secret so \Stripe\Webhook::constructEvent runs signature verification
        // (and throws SignatureVerificationException on our bogus signature) rather than
        // tripping on a null/empty secret.
        config(['services.stripe.webhook_secret' => 'whsec_dummy_test_secret']);
    }

    public function test_webhook_route_is_csrf_exempt_and_rejects_bad_signature_with_400(): void
    {
        // Raw JSON body + a clearly-invalid Stripe-Signature header.
        $response = $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => 't=1,v1=deadbeef', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['id' => 'evt_test', 'type' => 'checkout.session.completed'])
        );

        // CSRF-exempt: a tokenless web POST must not 419.
        $this->assertNotSame(419, $response->getStatusCode(), 'Webhook should be CSRF-exempt (no 419).');
        // Bad signature -> 400, not a 500.
        $response->assertStatus(400);
    }

    public function test_webhook_rejects_missing_signature_header_with_400(): void
    {
        $response = $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['id' => 'evt_test'])
        );

        $this->assertNotSame(419, $response->getStatusCode());
        $response->assertStatus(400);
    }

    public function test_webhook_rejects_empty_body_with_400(): void
    {
        $response = $this->call(
            'POST',
            '/stripe/webhook',
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => 't=1,v1=deadbeef', 'CONTENT_TYPE' => 'application/json'],
            ''
        );

        $this->assertNotSame(419, $response->getStatusCode());
        $response->assertStatus(400);
    }
}
