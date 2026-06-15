<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives Stripe webhook callbacks.
 *
 * IMPORTANT — CSRF: Stripe POSTs here with no CSRF token, so this route MUST be excluded from
 * CSRF verification. In Laravel 12 add the path to the `validateCsrfTokens` exception list in
 * bootstrap/app.php (see reply notes), e.g.:
 *
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->validateCsrfTokens(except: ['stripe/webhook']);
 *   })
 *
 * Signature verification (not CSRF) is what authenticates the request — handled in
 * StripeService::handleWebhook via the webhook secret.
 */
final class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeService $stripe,
    ) {}

    /**
     * POST endpoint. Returns 200 on success/ack, 400 on a bad signature or malformed payload.
     *
     * Wire as: Route::post('/stripe/webhook', StripeWebhookController::class)
     *          ->name('stripe.webhook');  (and add 'stripe/webhook' to CSRF exceptions)
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = (string) $request->header('Stripe-Signature', '');

        try {
            $this->stripe->handleWebhook($payload, $sig);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return response('Invalid signature', Response::HTTP_BAD_REQUEST);
        } catch (\UnexpectedValueException $e) {
            // Malformed JSON payload from constructEvent.
            Log::warning('Stripe webhook payload malformed.', ['error' => $e->getMessage()]);

            return response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        return response('OK', Response::HTTP_OK);
    }
}
