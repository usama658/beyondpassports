<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\EventChannel;
use App\Enums\OrderStatus;
use App\Jobs\SendWhatsAppUpdate;
use App\Models\Destination;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase-2 #25 — WhatsApp Business (Cloud API) lifecycle notifications.
 *
 * Verifies the EmailService/HubSpot mirror:
 *   - pre-launch (empty token): nothing sent, nothing recorded (graceful no-op);
 *   - configured (token + phone_id) + a `delivered` transition: exactly one WhatsApp send + one
 *     whatsapp order_event, idempotent on retry;
 *   - OrderService::transition() queues the SendWhatsAppUpdate job.
 */
final class WhatsAppNotifyTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://graph.facebook.com/*/messages';

    private function makeOrder(array $overrides = []): Order
    {
        $dest = Destination::create([
            'name' => 'Testlandia',
            'slug' => 'testlandia',
            'visa_type' => 'evisa',
            'tier_standard_gbp' => 39,
            'tier_express_gbp' => 59,
            'tier_premium_gbp' => 89,
            'govt_fee_gbp' => 20,
            'passport_validity_months' => 6,
        ]);

        return Order::create(array_merge([
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'phone' => '+44 7700 900123',
            'destination_id' => $dest->getKey(),
            'destination_name' => $dest->name,
            'tier' => 'standard',
            'service_fee' => 39,
            'govt_fee' => 20,
            'total' => 59,
            'status' => 'delivered',
        ], $overrides));
    }

    private function configureCredentials(): void
    {
        Config::set('services.whatsapp.token', 'test-token');
        Config::set('services.whatsapp.phone_id', '1234567890');
    }

    /** Pre-launch: empty token => no HTTP call and no order_event recorded. */
    public function test_empty_token_is_a_graceful_no_op(): void
    {
        Config::set('services.whatsapp.token', null);
        Config::set('services.whatsapp.phone_id', null);
        Http::fake();

        $order = $this->makeOrder();

        $sent = app(WhatsAppService::class)
            ->notifyStageChange($order, OrderStatus::AwaitingDecision, OrderStatus::Delivered);

        $this->assertFalse($sent);
        Http::assertNothingSent();
        $this->assertSame(
            0,
            $order->events()->where('channel', EventChannel::Whatsapp->value)->count(),
            'No whatsapp event should be recorded when credentials are absent.'
        );
    }

    /** No customer phone => no-op even with credentials configured. */
    public function test_missing_phone_is_a_no_op(): void
    {
        $this->configureCredentials();
        Http::fake();

        $order = $this->makeOrder(['phone' => null]);

        $sent = app(WhatsAppService::class)
            ->notifyStageChange($order, OrderStatus::AwaitingDecision, OrderStatus::Delivered);

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }

    /** Configured: a delivered transition sends exactly one WhatsApp + records one event. */
    public function test_delivered_sends_one_whatsapp_and_records_one_event(): void
    {
        $this->configureCredentials();
        Http::fake([self::ENDPOINT => Http::response(['messages' => [['id' => 'wamid.TEST']]], 200)]);

        $order = $this->makeOrder();

        $sent = app(WhatsAppService::class)
            ->notifyStageChange($order, OrderStatus::AwaitingDecision, OrderStatus::Delivered);

        $this->assertTrue($sent);
        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/1234567890/messages')
                && $request['messaging_product'] === 'whatsapp'
                // phone normalised to digits-only MSISDN.
                && $request['to'] === '447700900123'
                && $request['type'] === 'text';
        });

        $this->assertSame(
            1,
            $order->events()
                ->where('channel', EventChannel::Whatsapp->value)
                ->where('meta->event', WhatsAppService::EVENT_DELIVERED)
                ->count()
        );
    }

    /** Retry of the same milestone is idempotent: no second send, no second event. */
    public function test_retry_is_idempotent(): void
    {
        $this->configureCredentials();
        Http::fake([self::ENDPOINT => Http::response(['messages' => [['id' => 'wamid.TEST']]], 200)]);

        $order = $this->makeOrder();
        $svc = app(WhatsAppService::class);

        $first = $svc->notifyStageChange($order, OrderStatus::AwaitingDecision, OrderStatus::Delivered);
        $second = $svc->notifyStageChange($order->fresh(), OrderStatus::AwaitingDecision, OrderStatus::Delivered);

        $this->assertTrue($first);
        $this->assertFalse($second, 'Second send for the same (order, event) must be suppressed.');
        Http::assertSentCount(1);
        $this->assertSame(
            1,
            $order->events()->where('channel', EventChannel::Whatsapp->value)->count()
        );
    }

    /** OrderService::transition() queues the SendWhatsAppUpdate job on a real move. */
    public function test_transition_queues_the_whatsapp_job(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Facades\Mail::fake();

        // standard lane is cleared, so the eligibility gate lets it advance past `paid`.
        $order = $this->makeOrder(['status' => 'paid', 'eligibility' => 'standard']);

        app(\App\Services\OrderService::class)->transition($order, OrderStatus::AwaitingDocs);

        \Illuminate\Support\Facades\Queue::assertPushed(SendWhatsAppUpdate::class, 1);
    }
}
