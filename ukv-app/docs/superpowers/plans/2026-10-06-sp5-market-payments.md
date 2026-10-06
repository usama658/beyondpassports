# Per-Market Payments Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

> **Cross-spec note (2026-10-06, added on save):** Task 1 sets AE `tax_mode = inclusive` and `tax_note = "Prices include 5% UAE VAT."`. The UAE licensing memo (`research/2026-10-06-uae-visa-services-licensing-memo.md`) and SP3 spec section 17 rule that no VAT-inclusive claim may be printed until Beyond Passports is UAE VAT-registered. Until the owner rules, ship AE with `tax_mode => 'none'`, `tax_note => null` and adjust `test_tax_display_rules_per_market` accordingly.

**Goal:** let a `.com` market take the first instalment in its own currency (ZAR, AED, USD, CAD) on the UK Stripe account, charge the second instalment only when ops confirm an appointment, show fee + tax + "government and visa centre fees paid by you directly" honestly in price blocks and receipts, and gate all of it per market (prices set, accountant memo, checkout flag, Stripe live mode) without touching UK GBP behaviour.

**Architecture:** `orders` gains market/currency/instalment columns. `Market` gains the checkout gate. `StripeService` reads currency from the order's market, gets a pure payload builder for market sessions, a live-mode guard, and a remainder branch in the webhook. A small market funnel (`/{market}/apply`, `/checkout/{ref}`, `/confirmation/{ref}`) sits behind `EnsureMarketCheckoutOpen`. `AppointmentConfirmationService` is the single trigger for the second instalment, called from a Filament action.

**Tech Stack:** PHP 8.2+, Laravel 12, Blade, Filament v3, stripe-php (`\Stripe\Checkout\Session::constructFrom` in tests), PHPUnit sqlite in-memory, existing `App\Support\Market`, `market_url()`, `partials.market-price`, `EmailService::dispatch`, `OrderMailable`.

**Spec:** `ukv-app/docs/superpowers/specs/2026-10-06-sp5-market-payments-design.md`. Prerequisite: SP1 plan merged.

All paths below are relative to `ukv-app/`. Run every command from `ukv-app/`.

## Global Constraints

- UK GBP paths unchanged: `currencyFor()` returns `gbp` for UK orders and the checklist; UK `OrderPaid` email untouched; `CheckoutGuardTest`, `StripeWebhookTest`, `ChecklistPaymentTest` stay green (spec §6, §9).
- A null price renders nothing; `config('ukv.pricing.placeholder')` ("Get a quote") never appears on a `.com` page (spec §8, G2).
- Government and visa centre fees are never collected; `govt_fee` stays null on market orders (spec §4).
- No market Checkout Session is created in production unless the Stripe secret starts with `sk_live_` (spec §6, memory stripe-prod-test-mode).
- The second instalment is created only by `AppointmentConfirmationService::confirm()`; nowhere else (spec §10).
- No em-dashes in user-facing copy; no "guaranteed", "fast-track", "priority appointment", "early appointment" (SP1 constraints).
- Commit after every task. Never push (memory no-auto-deploy).

## Review Focus

1. Split mismatch (`price_upfront + price_remainder != price_total`) must close the gate. Test in Task 2.
2. The remainder webhook must be idempotent on `remainder_paid_at` and must not touch `paid_at`. Test in Task 4.
3. A UK order must still queue `OrderPaid`, never `MarketReceipt`; a market order the reverse. Test in Task 7.
4. Stripe refusal (test mode in production) must redirect with a message, never 500. Test in Task 6 (`test_checkout_redirects_with_message_when_stripe_refuses`).
5. The confirmation page must not show "Payment received" or fire the purchase event before `paid_at`. Test in Task 6.
6. Re-confirming an appointment must not create a second remainder session. Test in Task 8.

---

### Task 0: Preflight (no commit)

- [ ] **Step 1: Confirm SP1 and the three GBP literals**

Run: `test -f app/Support/Market.php && grep -n "'currency' => 'gbp'" app/Services/StripeService.php && php artisan test --filter='Market|Checkout|Stripe|Checklist'`
Expected: `Market.php` exists; exactly three `gbp` lines (around 97, 204, 361); tests PASS.

---

### Task 1: Per-market checkout config

**Files:**
- Modify: `config/ukv.php`, `.env.example`
- Test: `tests/Feature/MarketsCheckoutConfigTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketsCheckoutConfigTest extends TestCase
{
    public function test_every_market_has_checkout_keys_defaulting_closed(): void
    {
        foreach (config('ukv.markets') as $code => $m) {
            foreach (['checkout_enabled', 'tax_mode', 'tax_note', 'tax_memo'] as $k) {
                $this->assertArrayHasKey($k, $m, "$code missing $k");
            }
            $this->assertFalse($m['checkout_enabled'], "$code checkout open by default");
            $this->assertNull($m['tax_memo'], "$code has a tax memo by default");
            $this->assertContains($m['tax_mode'], ['none', 'inclusive', 'exclusive'], $code);
        }
    }

    public function test_tax_display_rules_per_market(): void
    {
        $this->assertSame('inclusive', config('ukv.markets.ae.tax_mode'));
        $this->assertSame('Prices include 5% UAE VAT.', config('ukv.markets.ae.tax_note'));
        $this->assertSame('exclusive', config('ukv.markets.ca.tax_mode'));
        $this->assertSame('Plus applicable taxes.', config('ukv.markets.ca.tax_note'));
        $this->assertSame('none', config('ukv.markets.us.tax_mode'));
        $this->assertNull(config('ukv.markets.us.tax_note'));
        $this->assertSame('none', config('ukv.markets.za.tax_mode'));
        $this->assertNull(config('ukv.markets.za.tax_note'));
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketsCheckoutConfigTest`
Expected: FAIL (keys missing).

- [ ] **Step 3: Add the keys**

In `config/ukv.php`, inside each market array after `'indexable' => ...,` add (replace `ZA` with the market code):

```php
            // SP5: checkout opens only when enabled + this flag + all three prices + tax_memo set
            // (Market::canCheckout). tax_memo = dated reference to the accountant memo for this market;
            // null keeps checkout closed. tax_mode/tax_note = display rule (spec 5).
            'checkout_enabled' => (bool) env('UKV_MARKET_ZA_CHECKOUT', false),
            'tax_mode'         => 'none',
            'tax_note'         => null,
            'tax_memo'         => env('UKV_MARKET_ZA_TAX_MEMO'),
```

For `ae` use `'tax_mode' => 'inclusive', 'tax_note' => 'Prices include 5% UAE VAT.'`; for `ca` use `'tax_mode' => 'exclusive', 'tax_note' => 'Plus applicable taxes.'`; `us` and `za` stay `none` / `null`.

Append to `.env.example`:

```
# Market checkout (SP5). Open per market ONLY after the launch gate in the SP5 spec section 12:
# accountant memo referenced below, Stripe on sk_live_, prices set in config, one live test charge.
UKV_MARKET_ZA_CHECKOUT=false
UKV_MARKET_ZA_TAX_MEMO=
UKV_MARKET_AE_CHECKOUT=false
UKV_MARKET_AE_TAX_MEMO=
UKV_MARKET_US_CHECKOUT=false
UKV_MARKET_US_TAX_MEMO=
UKV_MARKET_CA_CHECKOUT=false
UKV_MARKET_CA_TAX_MEMO=
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter='MarketsCheckoutConfigTest|MarketsConfigTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add config/ukv.php .env.example tests/Feature/MarketsCheckoutConfigTest.php
git commit -m "feat(payments): per-market checkout flag, tax display rule and accountant-memo reference"
```

---

### Task 2: Market checkout gate and money formatting

**Files:**
- Modify: `app/Support/Market.php`, `routes/web.php` (`/health/stripe`)
- Test: `tests/Unit/MarketCheckoutGateTest.php`

**Interfaces:**
- Produces: `Market::currencyLower()`, `checkoutEnabled()`, `taxMode()`, `taxNote()`, `taxMemo()`, `hasFullPrice()`, `canCheckout()`, `formatMoney(float)`; `/health/stripe` JSON gains `markets.<code>.{checkout_open,currency}`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Market;
use Tests\TestCase;

final class MarketCheckoutGateTest extends TestCase
{
    private function open(string $code = 'za'): void
    {
        config([
            "ukv.markets.$code.enabled" => true,
            "ukv.markets.$code.checkout_enabled" => true,
            "ukv.markets.$code.price_total" => 2490,
            "ukv.markets.$code.price_upfront" => 750,
            "ukv.markets.$code.price_remainder" => 1740,
            "ukv.markets.$code.tax_memo" => '2026-11-12 accountant memo',
        ]);
    }

    public function test_gate_opens_only_when_everything_is_set(): void
    {
        $this->assertFalse(Market::fromCode('za')->canCheckout());
        $this->open();
        $this->assertTrue(Market::fromCode('za')->canCheckout());
    }

    public function test_each_missing_piece_closes_the_gate(): void
    {
        foreach (['enabled' => false, 'checkout_enabled' => false, 'price_total' => null, 'price_upfront' => null, 'price_remainder' => null, 'tax_memo' => null] as $k => $v) {
            $this->open();
            config(["ukv.markets.za.$k" => $v]);
            $this->assertFalse(Market::fromCode('za')->canCheckout(), "$k should close the gate");
        }
    }

    public function test_split_mismatch_closes_the_gate(): void
    {
        $this->open();
        config(['ukv.markets.za.price_remainder' => 1700]);
        $this->assertFalse(Market::fromCode('za')->hasFullPrice());
        $this->assertFalse(Market::fromCode('za')->canCheckout());
    }

    public function test_uk_follows_the_apply_flag_only(): void
    {
        config(['ukv.apply.enabled' => false]);
        $this->assertFalse(Market::uk()->canCheckout());
        config(['ukv.apply.enabled' => true]);
        $this->assertTrue(Market::uk()->canCheckout());
        $this->assertSame('gbp', Market::uk()->currencyLower());
    }

    public function test_tax_and_money_helpers(): void
    {
        $this->assertSame('aed', Market::fromCode('ae')->currencyLower());
        $this->assertSame('inclusive', Market::fromCode('ae')->taxMode());
        $this->assertSame('Prices include 5% UAE VAT.', Market::fromCode('ae')->taxNote());
        $this->assertNull(Market::fromCode('us')->taxNote());
        $this->assertSame('R750', Market::fromCode('za')->formatMoney(750));
        $this->assertSame('AED 199', Market::fromCode('ae')->formatMoney(199.0));
        $this->assertSame('CA$249.50', Market::fromCode('ca')->formatMoney(249.5));
        $this->assertSame('£130', Market::uk()->formatMoney(130));
    }

    public function test_health_endpoint_lists_market_gates(): void
    {
        $this->open('ae');
        $r = $this->getJson('/health/stripe');
        $r->assertOk();
        $r->assertJsonPath('markets.ae.checkout_open', true);
        $r->assertJsonPath('markets.ae.currency', 'AED');
        $r->assertJsonPath('markets.za.checkout_open', false);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketCheckoutGateTest`
Expected: FAIL (methods undefined).

- [ ] **Step 3: Add the methods to `Market`**

After `priceRemainder()` in `app/Support/Market.php` add:

```php
    public function currencyLower(): string { return strtolower($this->currency()); }
    public function checkoutEnabled(): bool { return (bool) ($this->cfg['checkout_enabled'] ?? false); }
    public function taxMode(): string { return (string) ($this->cfg['tax_mode'] ?? 'none'); }
    public function taxNote(): ?string { $n = $this->cfg['tax_note'] ?? null; return $n ? (string) $n : null; }
    public function taxMemo(): ?string { $n = $this->cfg['tax_memo'] ?? null; return $n ? (string) $n : null; }

    /** All three prices set and the split sums to the total (to the cent). */
    public function hasFullPrice(): bool
    {
        $t = $this->priceTotal(); $u = $this->priceUpfront(); $r = $this->priceRemainder();

        return $t !== null && $u !== null && $r !== null && abs(($u + $r) - $t) < 0.005;
    }

    /**
     * SP5 spec 5. UK: the existing funnel switch only. Non-UK: enabled + checkout flag + full price +
     * accountant memo referenced. Stripe live mode is checked separately at session creation.
     */
    public function canCheckout(): bool
    {
        if ($this->isUk()) {
            return (bool) config('ukv.apply.enabled');
        }

        return $this->isEnabled() && $this->checkoutEnabled() && $this->hasFullPrice() && $this->taxMemo() !== null;
    }

    /** Symbol + amount; whole amounts without decimals ("R750", "AED 199", "CA$249.50"). */
    public function formatMoney(float $v): string
    {
        return $this->symbol().number_format($v, fmod($v, 1.0) === 0.0 ? 0 : 2);
    }
```

- [ ] **Step 4: Extend `/health/stripe`**

In `routes/web.php` inside the `/health/stripe` closure, add to the JSON array before the closing `]);`:

```php
        'markets' => collect(\App\Support\Market::codes())->mapWithKeys(fn (string $c) => [$c => [
            'checkout_open' => \App\Support\Market::fromCode($c)->canCheckout(),
            'currency' => \App\Support\Market::fromCode($c)->currency(),
        ]])->all(),
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketCheckoutGateTest|MarketTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Support/Market.php routes/web.php tests/Unit/MarketCheckoutGateTest.php
git commit -m "feat(payments): Market checkout gate (prices, split, memo, flag), tax helpers, health map"
```

---

### Task 3: `orders.market` and instalment columns

**Files:**
- Create: `database/migrations/2026_10_06_000002_add_market_payment_fields_to_orders.php`
- Modify: `app/Models/Order.php`
- Test: `tests/Feature/OrdersMarketColumnTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrdersMarketColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_to_uk_gbp(): void
    {
        $o = Order::create(['name' => 'UK Person', 'email' => 'uk@example.com', 'destination_name' => 'France']);
        $o->refresh();
        $this->assertSame('uk', $o->market);
        $this->assertSame('GBP', $o->currency);
        $this->assertTrue($o->isUkMarket());
        $this->assertSame('gbp', $o->resolveMarket()->currencyLower());
        $this->assertNull($o->upfront_fee);
        $this->assertNull($o->remainder_paid_at);
    }

    public function test_market_order_fields(): void
    {
        $o = Order::create(['name' => 'ZA Person', 'email' => 'za@example.com', 'destination_name' => 'Italy', 'market' => 'za', 'currency' => 'ZAR', 'service_fee' => 2490, 'upfront_fee' => 750, 'remainder_fee' => 1740, 'total' => 2490]);
        $o->refresh();
        $this->assertFalse($o->isUkMarket());
        $this->assertSame('South Africa', $o->resolveMarket()->label());
        $this->assertSame('750.00', (string) $o->upfront_fee);
    }

    public function test_unknown_market_code_falls_back_to_uk(): void
    {
        $o = Order::create(['name' => 'X', 'email' => 'x@example.com', 'market' => 'xx']);
        $this->assertTrue($o->resolveMarket()->isUk());
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=OrdersMarketColumnTest`
Expected: FAIL (no column `market`).

- [ ] **Step 3: Migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SP5 spec 4: source market + local currency + two-instalment fields. Defaults keep every existing
 * order UK/GBP. govt_fee stays null on market orders (never collected).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('market', 5)->default('uk')->after('order_ref')->index();
            $table->string('currency', 3)->default('GBP')->after('market');
            $table->decimal('upfront_fee', 10, 2)->nullable()->after('service_fee');
            $table->decimal('remainder_fee', 10, 2)->nullable()->after('upfront_fee');
            $table->timestamp('remainder_paid_at')->nullable()->after('paid_at');
            $table->string('remainder_checkout_url', 500)->nullable()->after('remainder_paid_at');
            $table->timestamp('appointment_confirmed_at')->nullable()->after('remainder_checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['market', 'currency', 'upfront_fee', 'remainder_fee', 'remainder_paid_at', 'remainder_checkout_url', 'appointment_confirmed_at']);
        });
    }
};
```

- [ ] **Step 4: Model**

In `app/Models/Order.php`: add `'market', 'currency', 'upfront_fee', 'remainder_fee', 'remainder_paid_at', 'remainder_checkout_url', 'appointment_confirmed_at',` to `$fillable` (new line after `'destination_id', ... 'paid_at',`). In `casts()` add `'upfront_fee' => 'decimal:2', 'remainder_fee' => 'decimal:2',` under money and `'remainder_paid_at' => 'datetime', 'appointment_confirmed_at' => 'datetime',` under datetimes. Add `use App\Support\Market;` and, before `// --- Relationships ---`:

```php
    /** Source market of this order; unknown or missing codes resolve to UK so old rows never break. */
    public function resolveMarket(): Market
    {
        try {
            return Market::fromCode((string) ($this->market ?: 'uk'));
        } catch (\InvalidArgumentException) {
            return Market::uk();
        }
    }

    public function isUkMarket(): bool
    {
        return $this->resolveMarket()->isUk();
    }
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='OrdersMarketColumnTest|OrderEventsTest|CheckoutGuardTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_10_06_000002_add_market_payment_fields_to_orders.php app/Models/Order.php tests/Feature/OrdersMarketColumnTest.php
git commit -m "feat(payments): orders.market, currency and two-instalment columns (UK/GBP defaults)"
```

---

### Task 4: StripeService: market currency, payload, live-mode guard, remainder webhook

**Files:**
- Modify: `app/Services/StripeService.php`, `app/Services/EmailService.php`
- Create: `app/Mail/MarketReceipt.php`, `resources/views/emails/market-receipt.blade.php`
- Test: `tests/Feature/StripeMarketCurrencyTest.php`

**Interfaces:**
- Produces: `StripeService::liveModeOk(string $secret, bool $production, bool $ukMarket): bool` (static), `currencyFor(Order): string`, `marketSessionPayload(Order, string $instalment, array $appointment = []): array`, `createMarketCheckoutSession(Order, string, array = []): string`, `markSessionPaid(Order, \Stripe\Checkout\Session): void`; `EmailService::sendMarketReceipt(Order, string $instalment): bool`, constants `EVENT_MARKET_RECEIPT_UPFRONT`, `EVENT_MARKET_RECEIPT_REMAINDER`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\MarketReceipt;
use App\Mail\OrderPaid;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Tests\TestCase;

final class StripeMarketCurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com']);
        Mail::fake();
        Bus::fake();
    }

    private function zaOrder(): Order
    {
        return Order::create(['name' => 'Thandi', 'email' => 'thandi@example.com', 'destination_name' => 'Italy', 'market' => 'za', 'currency' => 'ZAR', 'service_fee' => 2490, 'upfront_fee' => 750, 'remainder_fee' => 1740, 'total' => 2490]);
    }

    public function test_currency_for_uk_is_gbp_and_markets_lowercase(): void
    {
        $svc = app(StripeService::class);
        $this->assertSame('gbp', $svc->currencyFor(Order::create(['name' => 'UK', 'email' => 'u@example.com'])));
        $this->assertSame('zar', $svc->currencyFor($this->zaOrder()));
        $this->assertSame('aed', $svc->currencyFor(Order::create(['name' => 'A', 'email' => 'a@example.com', 'market' => 'ae'])));
        $this->assertSame('usd', $svc->currencyFor(Order::create(['name' => 'U', 'email' => 'us@example.com', 'market' => 'us'])));
        $this->assertSame('cad', $svc->currencyFor(Order::create(['name' => 'C', 'email' => 'c@example.com', 'market' => 'ca'])));
    }

    public function test_upfront_payload(): void
    {
        $o = $this->zaOrder();
        $p = app(StripeService::class)->marketSessionPayload($o, 'upfront');
        $this->assertSame('payment', $p['mode']);
        $this->assertSame('zar', $p['line_items'][0]['price_data']['currency']);
        $this->assertSame(75000, $p['line_items'][0]['price_data']['unit_amount']);
        $this->assertStringContainsString('First instalment', $p['line_items'][0]['price_data']['product_data']['name']);
        $this->assertStringContainsString('paid by you directly', $p['line_items'][0]['price_data']['product_data']['description']);
        $this->assertSame('upfront', $p['metadata']['instalment']);
        $this->assertSame('za', $p['metadata']['market']);
        $this->assertSame($o->order_ref, $p['metadata']['order_ref']);
        $this->assertSame($p['metadata'], $p['payment_intent_data']['metadata']);
        $this->assertSame('https://beyondpassports.com/za/confirmation/'.$o->order_ref.'?session_id={CHECKOUT_SESSION_ID}', $p['success_url']);
        $this->assertSame('https://beyondpassports.com/za/schengen-visa', $p['cancel_url']);
    }

    public function test_remainder_payload_carries_appointment_facts(): void
    {
        $o = $this->zaOrder();
        $p = app(StripeService::class)->marketSessionPayload($o, 'remainder', ['appointment_reference' => 'VFS-123', 'appointment_centre' => 'VFS Johannesburg', 'appointment_date' => '2026-12-01', 'confirmed_by' => 'Chloe']);
        $this->assertSame(174000, $p['line_items'][0]['price_data']['unit_amount']);
        $this->assertSame('remainder', $p['metadata']['instalment']);
        $this->assertSame('VFS-123', $p['metadata']['appointment_reference']);
        $this->assertSame('Chloe', $p['payment_intent_data']['metadata']['confirmed_by']);
        $this->assertStringContainsString('Second instalment', $p['line_items'][0]['price_data']['product_data']['name']);
    }

    public function test_payload_rejects_uk_orders_and_zero_amounts(): void
    {
        $svc = app(StripeService::class);
        try {
            $svc->marketSessionPayload(Order::create(['name' => 'UK', 'email' => 'u@example.com']), 'upfront');
            $this->fail('UK order accepted');
        } catch (\InvalidArgumentException) {
        }
        $this->expectException(\InvalidArgumentException::class);
        $svc->marketSessionPayload(Order::create(['name' => 'Z', 'email' => 'z@example.com', 'market' => 'za', 'upfront_fee' => null]), 'upfront');
    }

    public function test_live_mode_guard(): void
    {
        $this->assertTrue(StripeService::liveModeOk('rk_test_x', false, false), 'non-production is free to use test keys');
        $this->assertTrue(StripeService::liveModeOk('rk_test_x', true, true), 'UK rehearsal in production stays allowed');
        $this->assertFalse(StripeService::liveModeOk('rk_test_x', true, false), 'market charge on a test key in production refused');
        $this->assertFalse(StripeService::liveModeOk('sk_test_x', true, false));
        $this->assertTrue(StripeService::liveModeOk('sk_live_x', true, false));
    }

    public function test_mark_session_paid_routes_upfront_and_remainder_idempotently(): void
    {
        $o = $this->zaOrder();
        $svc = app(StripeService::class);

        $up = Session::constructFrom(['id' => 'cs_up', 'metadata' => ['instalment' => 'upfront'], 'currency' => 'zar', 'amount_total' => 75000, 'payment_intent' => 'pi_1']);
        $svc->markSessionPaid($o, $up);
        $o->refresh();
        $this->assertNotNull($o->paid_at);
        $this->assertNull($o->remainder_paid_at);
        Mail::assertQueued(MarketReceipt::class, fn (MarketReceipt $m) => $m->instalment === 'upfront');
        Mail::assertNotQueued(OrderPaid::class);

        $rem = Session::constructFrom(['id' => 'cs_rem', 'metadata' => ['instalment' => 'remainder'], 'currency' => 'zar', 'amount_total' => 174000, 'payment_intent' => 'pi_2']);
        $svc->markSessionPaid($o, $rem);
        $svc->markSessionPaid($o, $rem);
        $o->refresh();
        $this->assertNotNull($o->remainder_paid_at);
        $this->assertSame(2, $o->events()->count(), 'one event per instalment, retries ignored');
        Mail::assertQueued(MarketReceipt::class, fn (MarketReceipt $m) => $m->instalment === 'remainder');
    }

    public function test_uk_order_still_gets_order_paid_email(): void
    {
        $o = Order::create(['name' => 'UK', 'email' => 'u@example.com', 'destination_name' => 'France']);
        app(StripeService::class)->markSessionPaid($o, Session::constructFrom(['id' => 'cs_uk', 'metadata' => [], 'currency' => 'gbp', 'amount_total' => 4000]));
        Mail::assertQueued(OrderPaid::class);
        Mail::assertNotQueued(MarketReceipt::class);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=StripeMarketCurrencyTest`
Expected: FAIL (methods undefined, `MarketReceipt` missing).

- [ ] **Step 3: Create the receipt mailable and view**

`app/Mail/MarketReceipt.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Receipt for a market (.com) order instalment: local currency, tax note, government-fee line,
 * appointment facts for the second instalment (SP5 spec 9). UK orders keep OrderPaid.
 */
final class MarketReceipt extends OrderMailable
{
    public function __construct(Order $order, public string $instalment = 'upfront')
    {
        parent::__construct($order);
    }

    public function envelope(): Envelope
    {
        $m = $this->order->resolveMarket();
        $amount = $m->formatMoney((float) ($this->instalment === 'remainder' ? $this->order->remainder_fee : $this->order->upfront_fee));
        $billing = (string) config('ukv.email_billing');

        return new Envelope(
            subject: "Receipt: {$amount} received for your {$this->dest()} visa file ({$this->ref()})",
            replyTo: $billing !== '' ? [new Address($billing, 'Beyond Passports Billing')] : [],
        );
    }

    public function content(): Content
    {
        $o = $this->order;
        $m = $o->resolveMarket();
        $appt = $o->appointments()->latest('id')->first();
        $remainder = $this->instalment === 'remainder';

        return new Content(markdown: 'emails.market-receipt', with: $this->mergeData() + [
            'instalment' => $this->instalment,
            'instalmentLabel' => $remainder ? 'second instalment, due on your confirmed appointment' : 'first instalment, to start your file',
            'amount' => $m->formatMoney((float) ($remainder ? $o->remainder_fee : $o->upfront_fee)),
            'currency' => $m->currency(),
            'total' => $o->service_fee !== null ? $m->formatMoney((float) $o->service_fee) : null,
            'remainder' => (! $remainder && $o->remainder_fee) ? $m->formatMoney((float) $o->remainder_fee) : null,
            'taxNote' => $m->taxNote(),
            'appointmentRef' => $appt?->reference,
            'appointmentCentre' => $appt?->centre,
            'appointmentDate' => $appt?->scheduled_at?->format('j M Y'),
            'companyNo' => config('ukv.address.company_no') ?: '17331903',
        ]);
    }
}
```

`resources/views/emails/market-receipt.blade.php`:

```blade
@component('mail::message')
Hi {{ $name }},

We have received **{{ $amount }}** ({{ $currency }}) for your {{ $dest }} visa file, order {{ $ref }}. This is the {{ $instalmentLabel }}.

@if ($instalment === 'upfront' && $remainder)
Our total service fee is {{ $total }}. The remaining {{ $remainder }} is due only once your appointment is confirmed on the official visa centre site. No confirmed appointment, nothing more to pay.
@endif
@if ($instalment === 'remainder' && $appointmentRef)
This second instalment became due when your appointment {{ $appointmentRef }} at {{ $appointmentCentre }} on {{ $appointmentDate }} was confirmed by our team.
@endif
@if ($taxNote)
{{ $taxNote }}
@endif

Government and visa centre fees are paid by you directly to the official provider and are not included in this receipt.

Paid to Beyond Passports Ltd (Companies House {{ $companyNo }}). We never ask for payment to an individual.

@include('emails.partials.footer')
@endcomponent
```

- [ ] **Step 4: EmailService**

In `app/Services/EmailService.php` add `use App\Mail\MarketReceipt;`, constants next to `EVENT_ORDER_PAID`:

```php
    public const EVENT_MARKET_RECEIPT_UPFRONT = 'market_receipt_upfront';
    public const EVENT_MARKET_RECEIPT_REMAINDER = 'market_receipt_remainder';
```

and after `sendOrderPaid()`:

```php
    /** Market (.com) receipt per instalment; UK orders use sendOrderPaid (SP5 spec 9). */
    public function sendMarketReceipt(Order $order, string $instalment): bool
    {
        $event = $instalment === 'remainder' ? self::EVENT_MARKET_RECEIPT_REMAINDER : self::EVENT_MARKET_RECEIPT_UPFRONT;

        return $this->dispatch($order, $event, new MarketReceipt($order, $instalment));
    }
```

- [ ] **Step 5: StripeService**

Add `use App\Support\Market;` and these methods after the constructor:

```php
    /**
     * SP5 spec 6: in production, a market (non-UK) session may only be created on a live key.
     * UK keeps its test-mode rehearsal (memory stripe-prod-test-mode). Pure, so it is unit-tested.
     */
    public static function liveModeOk(string $secret, bool $production, bool $ukMarket): bool
    {
        return ! $production || $ukMarket || str_starts_with($secret, 'sk_live_');
    }

    /** Stripe currency code for an order: its market's currency, lowercased ('gbp' for UK). */
    public function currencyFor(Order $order): string
    {
        return $order->resolveMarket()->currencyLower();
    }

    /**
     * Pure Checkout Session payload for a market order instalment (SP5 spec 6). $appointment carries
     * reference/centre/date/confirmed_by for the remainder so the facts sit on the charge itself.
     *
     * @return array<string,mixed>
     */
    public function marketSessionPayload(Order $order, string $instalment, array $appointment = []): array
    {
        $market = $order->resolveMarket();
        if ($market->isUk()) {
            throw new \InvalidArgumentException('marketSessionPayload is for non-UK orders only.');
        }
        $amount = (float) ($instalment === 'remainder' ? $order->remainder_fee : $order->upfront_fee);
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Order {$order->order_ref} has no {$instalment} amount.");
        }

        $label = $instalment === 'remainder' ? 'Second instalment, due on confirmed appointment' : 'First instalment, to start';
        $description = sprintf(
            'Beyond Passports service fee (%s) for a %s visa file prepared from %s. Government and visa centre fees are paid by you directly to the official provider and are not included.',
            strtolower($label),
            $order->destination_name ?: 'Schengen',
            $market->label(),
        );
        $metadata = [
            'order_id' => (string) $order->getKey(),
            'order_ref' => (string) $order->order_ref,
            'market' => $market->code,
            'instalment' => $instalment,
        ];
        foreach ($appointment as $k => $v) {
            $metadata[(string) $k] = (string) $v;
        }

        return [
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $market->currencyLower(),
                    'unit_amount' => (int) round($amount * 100),
                    'product_data' => [
                        'name' => sprintf('Schengen visa preparation (%s): %s', $market->label(), $label),
                        'description' => $description,
                    ],
                ],
            ]],
            'payment_intent_data' => ['description' => $description, 'metadata' => $metadata],
            'metadata' => $metadata,
            'client_reference_id' => (string) $order->order_ref,
            'customer_email' => $order->email,
            'success_url' => market_url('/confirmation/'.$order->order_ref, $market).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => market_url('/schengen-visa', $market),
        ];
    }

    /** Create a market instalment session. Throws RuntimeException when the live-mode guard fails. */
    public function createMarketCheckoutSession(Order $order, string $instalment, array $appointment = []): string
    {
        if (! self::liveModeOk((string) config('services.stripe.secret'), app()->isProduction(), $order->isUkMarket())) {
            throw new \RuntimeException('Stripe production account is still in test mode; market checkout refused (memory stripe-prod-test-mode).');
        }
        $session = $this->client()->checkout->sessions->create($this->marketSessionPayload($order, $instalment, $appointment));

        return (string) $session->url;
    }

    /** Webhook dispatcher: remainder instalments go to markRemainderPaid, everything else to markOrderPaid. */
    public function markSessionPaid(Order $order, \Stripe\Checkout\Session $session): void
    {
        if (($session->metadata->instalment ?? 'upfront') === 'remainder') {
            $this->markRemainderPaid($order, $session);

            return;
        }
        $this->markOrderPaid($order, $session);
    }

    private function markRemainderPaid(Order $order, \Stripe\Checkout\Session $session): void
    {
        if ($order->remainder_paid_at !== null) {
            return; // idempotent on the remainder marker; paid_at untouched
        }
        $order->remainder_paid_at = Carbon::now();
        $order->remainder_checkout_url = null;
        $order->save();

        $order->events()->create([
            'occurred_at' => Carbon::now(),
            'agent' => 'stripe-webhook',
            'channel' => EventChannel::Internal,
            'type' => EventType::System,
            'text' => sprintf('Second instalment received via Stripe Checkout (%s).', $session->id ?? 'unknown session'),
            'meta' => ['stripe_session_id' => $session->id ?? null, 'stripe_payment_intent' => $session->payment_intent ?? null, 'amount_total' => $session->amount_total ?? null, 'currency' => $session->currency ?? null],
        ]);

        $this->emails->sendMarketReceipt($order, 'remainder');
    }
```

Edits to existing methods:
- `createCheckoutSession()`: first statement `if (! $order->isUkMarket()) { return $this->createMarketCheckoutSession($order, 'upfront'); }`; replace `'currency' => 'gbp',` with `'currency' => $this->currencyFor($order),`.
- `handleWebhook()`: replace `$this->markOrderPaid($order, $session);` with `$this->markSessionPaid($order, $session);`.
- `createChecklistSession()`: replace `'currency' => 'gbp',` with `'currency' => Market::uk()->currencyLower(), // checklist is a UK-only product`.
- `markOrderPaid()`: replace `$this->emails->sendOrderPaid($order);` with
  ```php
        if ($order->isUkMarket()) {
            $this->emails->sendOrderPaid($order);
        } else {
            $this->emails->sendMarketReceipt($order, 'upfront');
        }
  ```
- `createBespokeQuotePaymentLink()`: replace `'currency' => 'gbp',` with `'currency' => $this->currencyFor($order),` and the product name with `"Bespoke visa service ({$order->resolveMarket()->label()}), {$order->order_ref}"`.

- [ ] **Step 6: Run to verify it passes**

Run: `grep -c "'currency' => 'gbp'" app/Services/StripeService.php; php artisan test --filter='StripeMarketCurrencyTest|StripeWebhookTest|ChecklistPaymentTest|CheckoutGuardTest|WebhookEmailTest'`
Expected: `0`; all PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Services/StripeService.php app/Services/EmailService.php app/Mail/MarketReceipt.php resources/views/emails/market-receipt.blade.php tests/Feature/StripeMarketCurrencyTest.php
git commit -m "feat(payments): market currency from Market, pure session payload, live-mode guard, remainder webhook + receipt"
```

---

### Task 5: Fee block: tax line and checkout CTA, never a placeholder

**Files:**
- Modify: `resources/views/partials/market-price.blade.php`
- Test: `tests/Feature/MarketPriceBlockTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MarketPriceBlockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ukv.intl_base_url' => 'https://beyondpassports.com', 'ukv.pricing.placeholder' => 'Get a quote']);
        foreach (['za', 'ae', 'us', 'ca'] as $c) {
            config(["ukv.markets.$c.enabled" => true]);
        }
    }

    private function price(string $c, float $t, float $u, float $r): void
    {
        config(["ukv.markets.$c.price_total" => $t, "ukv.markets.$c.price_upfront" => $u, "ukv.markets.$c.price_remainder" => $r]);
    }

    public function test_null_price_renders_nothing_and_no_placeholder(): void
    {
        $r = $this->get('/za');
        $r->assertDontSee('Get a quote');
        $r->assertDontSee('mpr-total', false);
    }

    public function test_gate_closed_shows_price_with_whatsapp_start_and_no_apply_link(): void
    {
        $this->price('za', 2490, 750, 1740);
        $r = $this->get('/za');
        $r->assertSee('R2,490');
        $r->assertSee('Message us on WhatsApp to start');
        $r->assertDontSee('href="https://beyondpassports.com/za/apply"', false);
        $r->assertDontSee('Get a quote');
        $r->assertSee('Pay only to Beyond Passports Ltd');
    }

    public function test_gate_open_shows_start_link(): void
    {
        $this->price('za', 2490, 750, 1740);
        config(['ukv.markets.za.checkout_enabled' => true, 'ukv.markets.za.tax_memo' => 'memo']);
        $r = $this->get('/za');
        $r->assertSee('Start for R750');
        $r->assertSee('href="https://beyondpassports.com/za/apply"', false);
    }

    public function test_tax_lines_per_market(): void
    {
        $this->price('ae', 649, 199, 450);
        $this->get('/ae')->assertSee('Prices include 5% UAE VAT.');
        $this->price('ca', 249, 79, 170);
        $this->get('/ca')->assertSee('Plus applicable taxes.');
        $this->price('us', 249, 79, 170);
        $this->get('/us')->assertDontSee('VAT')->assertDontSee('applicable taxes');
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketPriceBlockTest`
Expected: FAIL (no "Message us on WhatsApp to start", no tax line).

- [ ] **Step 3: Edit the partial**

Replace the content of `resources/views/partials/market-price.blade.php` with:

```blade
{{-- Null-safe fee block (SP1 spec 6, SP5 spec 8). Renders nothing until price_total is set. Split only
     when BOTH parts are set. Tax line from Market::taxNote(). Checkout CTA only when the market gate
     is open; otherwise WhatsApp. Never the UK "Get a quote" placeholder. --}}
@php($m = $market ?? \App\Support\Market::current())
@if ($m->priceTotal() !== null)
  @php($fmt = fn (float $v) => $m->formatMoney($v))
  @php($waStart = $m->chatUrl('Hi Beyond Passports, I would like to start my Schengen visa file from '.$m->label().'. ['.strtoupper($m->code).']'))
  <section class="mpr" aria-label="Our fee">
    <p class="mpr-total"><b>{{ $fmt($m->priceTotal()) }}</b> our service fee, all in, per applicant.</p>
    @if ($m->priceUpfront() !== null && $m->priceRemainder() !== null)
      <p class="mpr-split">{{ $fmt($m->priceUpfront()) }} to start. {{ $fmt($m->priceRemainder()) }} only once your appointment is confirmed on the official visa centre site. No confirmed appointment, nothing more to pay.</p>
    @endif
    @if ($m->taxNote())<p class="mpr-tax">{{ $m->taxNote() }}</p>@endif
    <p class="mpr-note">Government visa fee and visa centre fee are paid by you directly at the appointment and are not included. You can also apply without us on the official portal for those fees alone.</p>
    <p class="mpr-note">Pay only to Beyond Passports Ltd, never to an individual.</p>
    @if ($m->canCheckout() && $m->priceUpfront() !== null)
      <a class="mpr-cta" href="{{ market_url('/apply', $m) }}">Start for {{ $fmt($m->priceUpfront()) }}</a>
    @else
      <a class="mpr-cta mpr-cta-wa" href="{{ $waStart }}" target="_blank" rel="noopener">Message us on WhatsApp to start</a>
    @endif
  </section>
  @once
  <style>
  .mpr{max-width:760px;margin:24px auto;padding:18px 20px;border:1px solid #dde3ec;border-radius:16px;background:#fff;font-family:"Outfit",system-ui,sans-serif;color:#16222E}
  .mpr-total{font-size:22px;margin:0 0 8px}.mpr-split{margin:0 0 8px;font-size:15px}.mpr-tax{margin:0 0 8px;font-size:13px;color:#2a3a47}.mpr-note{margin:0 0 6px;font-size:13px;color:#5d6b76}
  .mpr-cta{display:inline-block;margin-top:10px;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}.mpr-cta-wa{background:#1F6E63}
  </style>
  @endonce
@endif
```

- [ ] **Step 4: Run to verify it passes**

Run: `php artisan test --filter='MarketPriceBlockTest|MarketHomePageTest'`
Expected: PASS. If `MarketHomePageTest::test_home_hides_price_when_null_and_shows_total_only_when_split_missing` fails on `R2,490`, confirm `formatMoney(2490)` yields `R2,490` (whole amount, 0 decimals).

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/market-price.blade.php tests/Feature/MarketPriceBlockTest.php
git commit -m "feat(payments): fee block tax line, pay-only line, checkout CTA when gate open, WhatsApp otherwise"
```

---

### Task 6: Market funnel: apply, checkout, confirmation behind the gate

**Files:**
- Create: `app/Http/Middleware/EnsureMarketCheckoutOpen.php`, `app/Services/MarketOrderService.php`, `app/Http/Controllers/Market/MarketApplyController.php`, `app/Http/Controllers/Market/MarketCheckoutController.php`, `app/Http/Controllers/Market/MarketConfirmationController.php`, `resources/views/market/apply.blade.php`, `resources/views/market/confirmation.blade.php`
- Modify: `routes/web.php` (inside the `/{market}` group)
- Test: `tests/Feature/MarketApplyFlowTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class MarketApplyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'ukv.intl_base_url' => 'https://beyondpassports.com',
            'ukv.markets.za.enabled' => true, 'ukv.markets.za.checkout_enabled' => true, 'ukv.markets.za.tax_memo' => 'memo',
            'ukv.markets.za.price_total' => 2490, 'ukv.markets.za.price_upfront' => 750, 'ukv.markets.za.price_remainder' => 1740,
            'ukv.markets.ae.enabled' => true,
        ]);
    }

    private function post(): \Illuminate\Testing\TestResponse
    {
        return $this->post('/za/apply', ['name' => 'Thandi M', 'email' => 'thandi@example.com', 'phone' => '+27 68 000 0000', 'destination' => 'Italy', 'travel_date' => Carbon::today()->addDays(60)->toDateString(), 'begin_now' => '1']);
    }

    public function test_apply_is_404_when_gate_closed(): void
    {
        config(['ukv.markets.za.tax_memo' => null]);
        $this->get('/za/apply')->assertNotFound();
        $this->get('/ae/apply')->assertNotFound();
    }

    public function test_apply_renders_form_and_price(): void
    {
        $r = $this->get('/za/apply');
        $r->assertOk()->assertSee('R750')->assertSee('name="destination"', false)->assertSee('Liechtenstein')->assertDontSee('postcode');
    }

    public function test_store_creates_market_order_and_redirects_to_checkout(): void
    {
        $r = $this->post();
        $o = Order::query()->where('email', 'thandi@example.com')->firstOrFail();
        $r->assertRedirect('/za/checkout/'.$o->order_ref);
        $this->assertSame('za', $o->market);
        $this->assertSame('ZAR', $o->currency);
        $this->assertSame('750.00', (string) $o->upfront_fee);
        $this->assertSame('1740.00', (string) $o->remainder_fee);
        $this->assertSame('2490.00', (string) $o->service_fee);
        $this->assertNull($o->govt_fee);
        $this->assertNull($o->paid_at);
        $this->assertSame('Italy', $o->destination_name);
        $this->assertNotNull($o->immediate_performance_consent_at);
        $this->assertSame(1, $o->events()->count());
    }

    public function test_store_validates(): void
    {
        $this->post('/za/apply', ['name' => '', 'email' => 'nope', 'destination' => 'Narnia'])->assertSessionHasErrors(['name', 'email', 'phone', 'destination']);
    }

    public function test_checkout_rejects_order_from_another_market(): void
    {
        $o = Order::create(['name' => 'A', 'email' => 'a@example.com', 'market' => 'ae', 'currency' => 'AED', 'upfront_fee' => 199, 'remainder_fee' => 450, 'service_fee' => 649]);
        $this->get('/za/checkout/'.$o->order_ref)->assertNotFound();
    }

    public function test_checkout_redirects_with_message_when_stripe_refuses(): void
    {
        $this->mock(StripeService::class, function ($m) {
            $m->shouldReceive('createCheckoutSession')->once()->andThrow(new \RuntimeException('test mode'));
        });
        $this->post();
        $o = Order::query()->firstOrFail();
        $r = $this->get('/za/checkout/'.$o->order_ref);
        $r->assertRedirect('/za/schengen-visa');
        $r->assertSessionHas('status');
    }

    public function test_checkout_redirects_away_to_stripe(): void
    {
        $this->mock(StripeService::class, fn ($m) => $m->shouldReceive('createCheckoutSession')->once()->andReturn('https://checkout.stripe.com/c/pay/cs_test_1'));
        $this->post();
        $o = Order::query()->firstOrFail();
        $this->get('/za/checkout/'.$o->order_ref)->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');
    }

    public function test_confirmation_pending_then_paid(): void
    {
        $this->post();
        $o = Order::query()->firstOrFail();
        $r = $this->get('/za/confirmation/'.$o->order_ref);
        $r->assertOk()->assertSee('Payment pending')->assertDontSee('Payment received')->assertDontSee("'currency': 'ZAR'", false);
        $o->update(['paid_at' => Carbon::now()]);
        $r = $this->get('/za/confirmation/'.$o->order_ref);
        $r->assertSee('Payment received')->assertSee('R750')->assertSee('R1,740')->assertSee('paid by you directly')->assertSee("'currency': 'ZAR'", false);
        $this->get('/ae/confirmation/'.$o->order_ref)->assertNotFound();
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketApplyFlowTest`
Expected: FAIL (404 on `/za/apply` even when open).

- [ ] **Step 3: Middleware**

`app/Http/Middleware/EnsureMarketCheckoutOpen.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Market;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** 404 unless the bound market passes Market::canCheckout() (SP5 spec 5, 7). Runs after ResolveMarket. */
final class EnsureMarketCheckoutOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Market::current()->canCheckout()) {
            abort(404);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Order service**

`app/Services/MarketOrderService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EligibilityLane;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Enums\OrderStatus;
use App\Models\Destination;
use App\Models\Order;
use App\Support\Market;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Creates orders for .com markets (SP5 spec 7). Prices come from the Market, never from the UK
 * destination tiers. govt_fee stays null: government and visa centre fees are never collected.
 * Status starts at the pipeline entry stage `paid` with paid_at null until the Stripe webhook.
 */
final class MarketOrderService
{
    /** @param array{name:string,email:string,phone:string,destination:string,travel_date?:?string,begin_now?:mixed} $data */
    public function create(Market $market, array $data): Order
    {
        if ($market->isUk() || ! $market->canCheckout()) {
            throw new \LogicException("Market {$market->code} is not open for checkout.");
        }

        return DB::transaction(function () use ($market, $data): Order {
            $destination = Destination::query()->where('visa_type', 'Schengen')->where('name', $data['destination'])->first();

            $order = new Order;
            $order->market = $market->code;
            $order->currency = $market->currency();
            $order->name = $data['name'];
            $order->applicant_name = $data['name'];
            $order->email = $data['email'];
            $order->phone = $data['phone'];
            $order->residence_country = $market->label();
            $order->destination_id = $destination?->getKey();
            $order->destination_name = $destination?->name ?? $data['destination'];
            $order->travel_date = ! empty($data['travel_date']) ? Carbon::parse((string) $data['travel_date']) : null;
            $order->service_fee = $market->priceTotal();
            $order->upfront_fee = $market->priceUpfront();
            $order->remainder_fee = $market->priceRemainder();
            $order->govt_fee = null;
            $order->total = $market->priceTotal();
            $order->status = OrderStatus::Paid->value;
            $order->eligibility = EligibilityLane::Standard->value;
            if (! empty($data['begin_now'])) {
                $order->immediate_performance_consent_at = Carbon::now();
            }
            $order->save();

            $order->events()->create([
                'occurred_at' => Carbon::now(),
                'agent' => 'market-apply',
                'channel' => EventChannel::Internal,
                'type' => EventType::System,
                'text' => sprintf('Order created on beyondpassports.com/%s. Fee %s, split %s to start and %s on confirmed appointment. Government and visa centre fees not collected.', $market->code, $market->formatMoney((float) $market->priceTotal()), $market->formatMoney((float) $market->priceUpfront()), $market->formatMoney((float) $market->priceRemainder())),
                'meta' => ['market' => $market->code, 'currency' => $market->currency()],
            ]);

            return $order;
        });
    }
}
```

- [ ] **Step 5: Controllers**

`app/Http/Controllers/Market/MarketApplyController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Services\MarketOrderService;
use App\Support\Market;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Market intake (SP5 spec 7). No UK-only fields. Behind EnsureMarketCheckoutOpen. */
final class MarketApplyController extends Controller
{
    public function show(): Response
    {
        return response()->view('market.apply', ['market' => Market::current(), 'destinations' => MarketHubController::SCHENGEN]);
    }

    public function store(Request $request, MarketOrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'destination' => ['required', Rule::in(MarketHubController::SCHENGEN)],
            'travel_date' => ['nullable', 'date', 'after:today'],
            'begin_now' => ['nullable'],
        ]);

        $market = Market::current();
        $order = $orders->create($market, $data);

        return redirect()->route('market.checkout', ['market' => $market->code, 'order' => $order->order_ref]);
    }
}
```

`app/Http/Controllers/Market/MarketCheckoutController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeService;
use App\Support\Market;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/** Starts the first instalment for a market order (SP5 spec 7). The webhook alone marks it paid. */
final class MarketCheckoutController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function __invoke(Order $order): RedirectResponse
    {
        $market = Market::current();
        abort_unless($order->market === $market->code, 404);

        if ($order->paid_at !== null) {
            return redirect()->route('market.confirmation', ['market' => $market->code, 'order' => $order->order_ref]);
        }

        try {
            $url = $this->stripe->createCheckoutSession($order);
        } catch (\RuntimeException $e) {
            Log::error('Market checkout refused', ['order_ref' => $order->order_ref, 'market' => $market->code, 'error' => $e->getMessage()]);

            return redirect()->route('market.hub', ['market' => $market->code])
                ->with('status', 'Checkout is not open yet. Message us on WhatsApp and we will send a payment link.');
        }

        return redirect()->away($url);
    }
}
```

`app/Http/Controllers/Market/MarketConfirmationController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Market;
use Illuminate\Http\Response;

/** Return page after Stripe. Shows "received" only once the webhook set paid_at (SP5 spec 8). */
final class MarketConfirmationController extends Controller
{
    public function __invoke(Order $order): Response
    {
        $market = Market::current();
        abort_unless($order->market === $market->code, 404);

        return response()->view('market.confirmation', ['market' => $market, 'order' => $order]);
    }
}
```

- [ ] **Step 6: Views**

`resources/views/market/apply.blade.php`:

```blade
<!doctype html>
<html lang="{{ $market->locale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Start your Schengen visa file from {{ $market->label() }} | Beyond Passports</title>
<meta name="robots" content="noindex, nofollow">
@include('partials.analytics-head')
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.ap{max-width:760px;margin:0 auto;padding:40px 24px}.ap h1{font-size:clamp(24px,3.6vw,36px);margin:0 0 10px}
.ap form{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:20px;display:grid;gap:12px}
.ap label{font-weight:600;font-size:14px}.ap input,.ap select{width:100%;padding:10px;border:1px solid #cfd7e2;border-radius:10px;font:inherit}
.ap .err{color:#b91c1c;font-size:13px}.ap button{background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border:0;border-radius:12px;cursor:pointer}
.ap .consent{display:flex;gap:8px;align-items:flex-start;font-size:13px}
@media (max-width:560px){.ap{padding:28px 16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<main class="ap">
  <h1>Start your Schengen visa file</h1>
  <p>Pay the first instalment now. The second is due only once your appointment is confirmed on the official visa centre site.</p>
  @include('partials.market-price', ['market' => $market])
  <form method="post" action="{{ market_url('/apply', $market) }}">
    @csrf
    <div><label for="name">Full name (as on passport)</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="160">@error('name')<div class="err">{{ $message }}</div>@enderror</div>
    <div><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="err">{{ $message }}</div>@enderror</div>
    <div><label for="phone">WhatsApp number</label><input id="phone" name="phone" value="{{ old('phone') }}" required maxlength="40">@error('phone')<div class="err">{{ $message }}</div>@enderror</div>
    <div><label for="destination">Destination (main country of your trip)</label>
      <select id="destination" name="destination" required>
        <option value="">Choose</option>
        @foreach ($destinations as $d)<option value="{{ $d }}" @selected(old('destination') === $d)>{{ $d }}</option>@endforeach
      </select>@error('destination')<div class="err">{{ $message }}</div>@enderror</div>
    <div><label for="travel_date">Planned travel date (optional)</label><input id="travel_date" name="travel_date" type="date" value="{{ old('travel_date') }}">@error('travel_date')<div class="err">{{ $message }}</div>@enderror</div>
    <label class="consent"><input type="checkbox" name="begin_now" value="1" checked> I ask Beyond Passports to begin work on my file straight away. I understand the fee is for preparation and review, not for the consulate's decision, and that government and visa centre fees are paid by me directly.</label>
    <button type="submit">Continue to secure payment</button>
  </form>
</main>
@include('partials.market-trust-strip', ['market' => $market])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

`resources/views/market/confirmation.blade.php`:

```blade
@php
  $m = $market; $paid = $order->paid_at !== null;
  $wa = $m->chatUrl('Hi Beyond Passports, my order is '.$order->order_ref.'. ['.strtoupper($m->code).']');
@endphp
<!doctype html>
<html lang="{{ $m->locale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg?v=2') }}" type="image/svg+xml">
<title>Order {{ $order->order_ref }} | Beyond Passports</title>
<meta name="robots" content="noindex, nofollow">
@include('partials.analytics-head')
@if ($paid && $order->upfront_fee !== null)
<script>window.dataLayer=window.dataLayer||[];window.dataLayer.push({event:'purchase',ecommerce:{'transaction_id':'{{ $order->order_ref }}','value':{{ round((float) $order->upfront_fee, 2) }},'currency': '{{ $order->currency }}'}});</script>
@endif
<style>
body{margin:0;font-family:"Outfit",system-ui,sans-serif;color:#16222E;background:#F4F5F6}
.cf{max-width:760px;margin:0 auto;padding:40px 24px}.cf h1{font-size:clamp(24px,3.6vw,36px);margin:0 0 10px}
.cf .card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:20px;line-height:1.55}
.cf .cta{display:inline-block;margin-top:12px;background:#155E7A;color:#fff;font-weight:700;padding:12px 18px;border-radius:12px;text-decoration:none}
@media (max-width:560px){.cf{padding:28px 16px}}
</style>
</head>
<body>
@include('partials.lp-chrome')
<main class="cf">
  <h1>{{ $paid ? 'Payment received' : 'Payment pending' }}</h1>
  <div class="card">
    <p>Order <b>{{ $order->order_ref }}</b>, {{ $order->destination_name }} visa file from {{ $m->label() }}.</p>
    @if ($paid)
      <p>We have received <b>{{ $m->formatMoney((float) $order->upfront_fee) }}</b> ({{ $order->currency }}), the first instalment. Your receipt is on its way by email.</p>
      @if ($order->remainder_fee)<p>The remaining <b>{{ $m->formatMoney((float) $order->remainder_fee) }}</b> is due only once your appointment is confirmed on the official visa centre site.</p>@endif
    @else
      <p>If you have just paid, this page updates within a minute once Stripe confirms the payment. If you did not complete payment, nothing has been charged.</p>
    @endif
    @if ($m->taxNote())<p>{{ $m->taxNote() }}</p>@endif
    <p>Government visa fee and visa centre fee are paid by you directly at the appointment and are not included.</p>
    <p>Next: a named consultant messages you on WhatsApp with your document checklist.</p>
    <a class="cta" href="{{ $wa }}" target="_blank" rel="noopener">Message your consultant</a>
  </div>
</main>
@include('partials.market-trust-strip', ['market' => $m])
@include('partials.lp-footer')
@include('partials.utm-capture')
</body>
</html>
```

- [ ] **Step 7: Routes**

Inside the `/{market}` group in `routes/web.php`, after the `market.tours` line add:

```php
        // SP5: market funnel. Apply + checkout only when Market::canCheckout(); confirmation always
        // (a paid client must be able to return), but only for an order of this market.
        Route::middleware(\App\Http\Middleware\EnsureMarketCheckoutOpen::class)->group(function () {
            Route::get('/apply', [\App\Http\Controllers\Market\MarketApplyController::class, 'show'])->name('market.apply');
            Route::post('/apply', [\App\Http\Controllers\Market\MarketApplyController::class, 'store'])->middleware('throttle:12,1')->name('market.apply.store');
            Route::get('/checkout/{order:order_ref}', \App\Http\Controllers\Market\MarketCheckoutController::class)->name('market.checkout');
        });
        Route::get('/confirmation/{order:order_ref}', \App\Http\Controllers\Market\MarketConfirmationController::class)->name('market.confirmation');
```

- [ ] **Step 8: Run to verify it passes**

Run: `php artisan test --filter='MarketApplyFlowTest|MarketRoutingTest|CheckoutGuardTest'`
Expected: PASS. If `test_store_validates` reports `phone` missing from errors, confirm the `phone` rule is `required` (it is) and that the test payload omits it (it does).

- [ ] **Step 9: Commit**

```bash
git add app/Http/Middleware/EnsureMarketCheckoutOpen.php app/Services/MarketOrderService.php app/Http/Controllers/Market/MarketApplyController.php app/Http/Controllers/Market/MarketCheckoutController.php app/Http/Controllers/Market/MarketConfirmationController.php resources/views/market/apply.blade.php resources/views/market/confirmation.blade.php routes/web.php tests/Feature/MarketApplyFlowTest.php
git commit -m "feat(payments): market apply/checkout/confirmation behind per-market gate; orders priced from Market"
```

---

### Task 7: Receipt content and remainder-due email

**Files:**
- Create: `app/Mail/RemainderDue.php`, `resources/views/emails/remainder-due.blade.php`
- Modify: `app/Services/EmailService.php`
- Test: `tests/Feature/MarketReceiptEmailTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\MarketReceipt;
use App\Mail\RemainderDue;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarketReceiptEmailTest extends TestCase
{
    use RefreshDatabase;

    private function aeOrder(): Order
    {
        $o = Order::create(['name' => 'Ayesha', 'email' => 'a@example.com', 'destination_name' => 'Germany', 'market' => 'ae', 'currency' => 'AED', 'service_fee' => 649, 'upfront_fee' => 199, 'remainder_fee' => 450, 'total' => 649, 'remainder_checkout_url' => 'https://checkout.stripe.com/c/pay/cs_test_rem']);
        $o->appointments()->create(['centre' => 'VFS Dubai Wafi', 'reference' => 'VFS-AE-77', 'scheduled_at' => '2026-12-01', 'status' => 'booked']);

        return $o;
    }

    public function test_upfront_receipt_content(): void
    {
        $html = (new MarketReceipt($this->aeOrder(), 'upfront'))->render();
        $this->assertStringContainsString('AED 199', $html);
        $this->assertStringContainsString('(AED)', $html);
        $this->assertStringContainsString('first instalment', $html);
        $this->assertStringContainsString('remaining AED 450', $html);
        $this->assertStringContainsString('Prices include 5% UAE VAT.', $html);
        $this->assertStringContainsString('paid by you directly to the official provider', $html);
        $this->assertStringContainsString('Companies House 17331903', $html);
        $this->assertStringContainsString('Independent service', $html, 'mandatory compliance footer present');
        $this->assertStringNotContainsString("\u{2014}", $html);
    }

    public function test_remainder_receipt_names_the_appointment(): void
    {
        $html = (new MarketReceipt($this->aeOrder(), 'remainder'))->render();
        $this->assertStringContainsString('AED 450', $html);
        $this->assertStringContainsString('VFS-AE-77', $html);
        $this->assertStringContainsString('VFS Dubai Wafi', $html);
        $this->assertStringContainsString('1 Dec 2026', $html);
        $this->assertStringContainsString('confirmed by our team', $html);
    }

    public function test_remainder_due_email_carries_link_and_facts(): void
    {
        $html = (new RemainderDue($this->aeOrder()))->render();
        $this->assertStringContainsString('https://checkout.stripe.com/c/pay/cs_test_rem', $html);
        $this->assertStringContainsString('AED 450', $html);
        $this->assertStringContainsString('VFS-AE-77', $html);
        $this->assertStringContainsString('never ask for payment to an individual', $html);
    }

    public function test_us_receipt_has_no_tax_line(): void
    {
        $o = Order::create(['name' => 'Raj', 'email' => 'r@example.com', 'destination_name' => 'Italy', 'market' => 'us', 'currency' => 'USD', 'service_fee' => 249, 'upfront_fee' => 79, 'remainder_fee' => 170]);
        $html = (new MarketReceipt($o, 'upfront'))->render();
        $this->assertStringContainsString('$79', $html);
        $this->assertStringNotContainsString('VAT', $html);
        $this->assertStringNotContainsString('applicable taxes', $html);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=MarketReceiptEmailTest`
Expected: FAIL (`RemainderDue` missing).

- [ ] **Step 3: Mailable and view**

`app/Mail/RemainderDue.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Sent when ops confirm the appointment on a market order with an unpaid second instalment (SP5 spec 9). */
final class RemainderDue extends OrderMailable
{
    public function envelope(): Envelope
    {
        $billing = (string) config('ukv.email_billing');

        return new Envelope(
            subject: "Your appointment is confirmed: second instalment for {$this->dest()} ({$this->ref()})",
            replyTo: $billing !== '' ? [new Address($billing, 'Beyond Passports Billing')] : [],
        );
    }

    public function content(): Content
    {
        $o = $this->order;
        $m = $o->resolveMarket();
        $appt = $o->appointments()->latest('id')->first();

        return new Content(markdown: 'emails.remainder-due', with: $this->mergeData() + [
            'amount' => $m->formatMoney((float) $o->remainder_fee),
            'currency' => $m->currency(),
            'payUrl' => (string) $o->remainder_checkout_url,
            'appointmentRef' => $appt?->reference,
            'appointmentCentre' => $appt?->centre,
            'appointmentDate' => $appt?->scheduled_at?->format('j M Y'),
            'taxNote' => $m->taxNote(),
            'companyNo' => config('ukv.address.company_no') ?: '17331903',
        ]);
    }
}
```

`resources/views/emails/remainder-due.blade.php`:

```blade
@component('mail::message')
Hi {{ $name }},

Your appointment for your {{ $dest }} visa is confirmed on the official visa centre site: reference {{ $appointmentRef }}, {{ $appointmentCentre }}, {{ $appointmentDate }}.

As agreed when you started, the second instalment of **{{ $amount }}** ({{ $currency }}) is now due.

@component('mail::button', ['url' => $payUrl])
Pay {{ $amount }} securely
@endcomponent

@if ($taxNote)
{{ $taxNote }}
@endif

Government and visa centre fees are paid by you directly at the appointment and are not included.

Payment goes to Beyond Passports Ltd (Companies House {{ $companyNo }}). We never ask for payment to an individual. If this link does not open, reply to this email and we will resend it.

@include('emails.partials.footer')
@endcomponent
```

- [ ] **Step 4: EmailService**

Add `use App\Mail\RemainderDue;`, constant `public const EVENT_REMAINDER_DUE = 'remainder_due';` and:

```php
    /** remainder_due: ops confirmed the appointment on a market order; the second-instalment link is ready. */
    public function sendRemainderDue(Order $order): bool
    {
        return $this->dispatch($order, self::EVENT_REMAINDER_DUE, new RemainderDue($order));
    }
```

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='MarketReceiptEmailTest|WebhookEmailTest'`
Expected: PASS. If `render()` complains about a missing `$email` key, confirm `mergeData()` is spread first (it is).

- [ ] **Step 6: Commit**

```bash
git add app/Mail/RemainderDue.php resources/views/emails/remainder-due.blade.php app/Services/EmailService.php tests/Feature/MarketReceiptEmailTest.php
git commit -m "feat(payments): market receipts (local currency, tax note, govt-fee line) and remainder-due email"
```

---

### Task 8: Appointment confirmation = second instalment trigger (service + Filament action)

**Files:**
- Create: `app/Services/AppointmentConfirmationService.php`
- Modify: `app/Filament/Resources/OrderResource.php`
- Test: `tests/Feature/ConfirmAppointmentTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\AppointmentBooked;
use App\Mail\RemainderDue;
use App\Models\Order;
use App\Services\AppointmentConfirmationService;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class ConfirmAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_market_order_gets_remainder_link_once(): void
    {
        $this->mock(StripeService::class, function ($m) {
            $m->shouldReceive('createMarketCheckoutSession')->once()
                ->withArgs(fn (Order $o, string $inst, array $appt) => $inst === 'remainder' && $appt['appointment_reference'] === 'VFS-123' && $appt['confirmed_by'] === 'Chloe')
                ->andReturn('https://checkout.stripe.com/c/pay/cs_test_rem');
        });
        $o = Order::create(['name' => 'Thandi', 'email' => 't@example.com', 'destination_name' => 'Italy', 'market' => 'za', 'currency' => 'ZAR', 'upfront_fee' => 750, 'remainder_fee' => 1740, 'paid_at' => Carbon::now()]);

        $svc = app(AppointmentConfirmationService::class);
        $appt = $svc->confirm($o, 'VFS Johannesburg', 'VFS-123', Carbon::parse('2026-12-01'), 'Chloe');
        $o->refresh();

        $this->assertSame('booked', $appt->status->value);
        $this->assertNotNull($o->appointment_confirmed_at);
        $this->assertSame('https://checkout.stripe.com/c/pay/cs_test_rem', $o->remainder_checkout_url);
        $this->assertStringContainsString('VFS-123', $o->events()->latest('id')->first()->text);
        $this->assertStringContainsString('Chloe', $o->events()->latest('id')->first()->text);
        Mail::assertQueued(RemainderDue::class);

        $first = $o->appointment_confirmed_at;
        $svc->confirm($o, 'VFS Johannesburg', 'VFS-124', Carbon::parse('2026-12-03'), 'Chloe'); // reschedule
        $o->refresh();
        $this->assertTrue($o->appointment_confirmed_at->equalTo($first), 'first confirmation stamp kept');
        $this->assertSame(2, $o->appointments()->count());
    }

    public function test_uk_order_never_calls_stripe(): void
    {
        $this->mock(StripeService::class, fn ($m) => $m->shouldNotReceive('createMarketCheckoutSession'));
        $o = Order::create(['name' => 'UK', 'email' => 'u@example.com', 'destination_name' => 'France']);
        app(AppointmentConfirmationService::class)->confirm($o, 'TLS London', 'TLS-1', Carbon::parse('2026-12-01'), 'Sam');
        $o->refresh();
        $this->assertNotNull($o->appointment_confirmed_at);
        $this->assertNull($o->remainder_checkout_url);
        Mail::assertQueued(AppointmentBooked::class);
        Mail::assertNotQueued(RemainderDue::class);
    }

    public function test_already_paid_remainder_creates_no_session(): void
    {
        $this->mock(StripeService::class, fn ($m) => $m->shouldNotReceive('createMarketCheckoutSession'));
        $o = Order::create(['name' => 'A', 'email' => 'a@example.com', 'market' => 'ae', 'currency' => 'AED', 'upfront_fee' => 199, 'remainder_fee' => 450, 'remainder_paid_at' => Carbon::now()]);
        app(AppointmentConfirmationService::class)->confirm($o, 'VFS Dubai', 'X', Carbon::parse('2026-12-01'), 'Sam');
        Mail::assertNotQueued(RemainderDue::class);
    }
}
```

- [ ] **Step 2: Run to verify it fails**

Run: `php artisan test --filter=ConfirmAppointmentTest`
Expected: FAIL (class missing).

- [ ] **Step 3: Service**

`app/Services/AppointmentConfirmationService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\EventChannel;
use App\Enums\EventType;
use App\Models\Appointment;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The single trigger for the second instalment (SP5 spec 10). Ops record the appointment exactly
 * as confirmed on the official site (centre, reference, date). That fact, with the confirmer's
 * name, goes on the order event and on the Stripe charge metadata (chargeback evidence). A market
 * order with an unpaid remainder gets its Checkout URL and the RemainderDue email once; re-confirming
 * (reschedule) never creates a second session. UK orders: appointment + event + existing email only.
 */
final class AppointmentConfirmationService
{
    public function __construct(
        private readonly StripeService $stripe,
        private readonly EmailService $emails,
    ) {}

    public function confirm(Order $order, string $centre, string $reference, Carbon $scheduledAt, string $confirmedBy): Appointment
    {
        $confirmedBy = trim($confirmedBy) !== '' ? trim($confirmedBy) : 'ops';

        [$appointment, $first] = DB::transaction(function () use ($order, $centre, $reference, $scheduledAt, $confirmedBy): array {
            $appointment = $order->appointments()->create([
                'centre' => $centre,
                'reference' => $reference,
                'scheduled_at' => $scheduledAt->toDateString(),
                'status' => AppointmentStatus::Booked->value,
            ]);

            $first = $order->appointment_confirmed_at === null;
            if ($first) {
                $order->appointment_confirmed_at = Carbon::now();
                $order->save();
            }

            $order->events()->create([
                'occurred_at' => Carbon::now(),
                'agent' => $confirmedBy,
                'channel' => EventChannel::Internal,
                'type' => EventType::System,
                'text' => sprintf('Appointment confirmed by %s: %s, %s, reference %s.', $confirmedBy, $centre, $scheduledAt->format('j M Y'), $reference),
                'meta' => ['centre' => $centre, 'reference' => $reference, 'scheduled_at' => $scheduledAt->toDateString(), 'confirmed_by' => $confirmedBy, 'first_confirmation' => $first],
            ]);

            return [$appointment, $first];
        });

        $needsRemainder = $first && ! $order->isUkMarket() && (float) $order->remainder_fee > 0 && $order->remainder_paid_at === null;
        if ($needsRemainder) {
            // Outside the transaction: external call.
            $url = $this->stripe->createMarketCheckoutSession($order, 'remainder', [
                'appointment_reference' => $reference,
                'appointment_centre' => $centre,
                'appointment_date' => $scheduledAt->toDateString(),
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => Carbon::now()->toIso8601String(),
            ]);
            $order->remainder_checkout_url = $url;
            $order->save();
            $this->emails->sendRemainderDue($order);
        } else {
            $this->emails->sendAppointmentBooked($order);
        }

        return $appointment;
    }
}
```

- [ ] **Step 4: Filament action and market fields**

In `app/Filament/Resources/OrderResource.php`:

Inside the table `->actions([` array, before `Action::make('advanceStage')`, add:

```php
                Action::make('confirmAppointment')
                    ->label('Confirm appointment')
                    ->icon('heroicon-o-calendar-days')
                    ->form([
                        TextInput::make('centre')->label('Centre')->required()->maxLength(160),
                        TextInput::make('reference')->label('Official booking reference')->required()->maxLength(120),
                        DatePicker::make('scheduled_at')->label('Appointment date')->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('Records the appointment exactly as confirmed on the official site. For beyondpassports.com orders this issues the second-instalment payment link and emails it to the client.')
                    ->action(function (Order $record, array $data): void {
                        app(\App\Services\AppointmentConfirmationService::class)->confirm(
                            $record,
                            (string) $data['centre'],
                            (string) $data['reference'],
                            \Illuminate\Support\Carbon::parse((string) $data['scheduled_at']),
                            (string) (auth()->user()?->name ?? 'ops'),
                        );
                        \Filament\Notifications\Notification::make()->title('Appointment confirmed')->success()->send();
                    }),
```

In the form `Section::make('Status & Tier')` schema add first:

```php
                    Select::make('market')
                        ->label('Market')
                        ->options(\App\Filament\Pages\UpdateAvailability::marketOptions())
                        ->default('uk')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Set at creation by the funnel; shown for reference.'),
```

(If SP4 is not yet merged, use `->options(['uk' => 'UK'] + collect(\App\Support\Market::codes())->mapWithKeys(fn ($c) => [$c => \App\Support\Market::fromCode($c)->label()])->all())`.)

In `Section::make('Fees')` replace each `->prefix('£')` with `->prefix(fn (?Order $record): string => $record?->resolveMarket()->symbol() ?? '£')`. In the table, after `TextColumn::make('order_ref')` add `TextColumn::make('market')->label('Market')->badge()->formatStateUsing(fn (?string $state): string => strtoupper($state ?: 'uk')),` and replace `->money('GBP')` on the `total` column with `->money(fn (Order $record): string => $record->currency ?: 'GBP')`.

- [ ] **Step 5: Run to verify it passes**

Run: `php artisan test --filter='ConfirmAppointmentTest|AdminPanelSmokeTest|OrderEventsTest'`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/AppointmentConfirmationService.php app/Filament/Resources/OrderResource.php tests/Feature/ConfirmAppointmentTest.php
git commit -m "feat(payments): appointment confirmation service triggers second instalment; Filament action + market column"
```

---

### Task 9: Full suite, runbook launch gate, spec status

**Files:**
- Modify: `docs/GO-LIVE-RUNBOOK.md`, spec status line.

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: all PASS.

- [ ] **Step 2: Runbook**

Append to `docs/GO-LIVE-RUNBOOK.md`:

```markdown
## Market checkout launch gate (beyondpassports.com, per market)

Spec: docs/superpowers/specs/2026-10-06-sp5-market-payments-design.md section 12. Do these in order.

1. Accountant memo for the market received. Put its dated reference in the server `.env`:
   `UKV_MARKET_<CODE>_TAX_MEMO="2026-11-12 Smith & Co VAT memo"`. Null keeps checkout closed.
2. Stripe live: `STRIPE_SECRET=sk_live_...` + live `STRIPE_WEBHOOK_SECRET`, then
   `php artisan config:cache`. `GET /health/stripe` must show `"secret_mode":"live"`. Until then the
   app refuses to create any market session (UK test-mode rehearsal unaffected).
3. Prices in `config/ukv.php` for the market: `price_total`, `price_upfront`, `price_remainder`
   (split must sum to total). Check `/<code>` fee block and tax line.
4. `UKV_MARKET_<CODE>_CHECKOUT=true`, `config:cache`. `/health/stripe` shows `markets.<code>.checkout_open: true`.
5. One live local-currency charge end to end with a real card: `/<code>/apply`, pay the first
   instalment, receive the receipt, Admin > Orders > Confirm appointment, pay the remainder link,
   receive the second receipt. Then refund both charges in Stripe and note the Stripe fee + FX.
6. Only now open ads for that market (SP7).
```

- [ ] **Step 3: Spec status**

In the spec change `**Status:** draft for owner review.` to `**Status:** implemented on branch (plan 2026-10-06-sp5-market-payments.md); awaiting owner acceptance against section 11 and sign-off of section 12 per market.`

- [ ] **Step 4: Commit**

```bash
git add docs/GO-LIVE-RUNBOOK.md docs/superpowers/specs/2026-10-06-sp5-market-payments-design.md
git commit -m "docs(payments): market checkout launch gate runbook; spec status"
```

---

## Self-review (done at writing time)

- Spec coverage: 4 Task 3; 5 Tasks 1, 2; 6 Task 4 (currency, payload, live guard, webhook branch; three `gbp` literals replaced, grep count 0); 7 Task 6; 8 Tasks 5, 6 (confirmation page); 9 Tasks 4 (`MarketReceipt`), 7 (`RemainderDue`); 10 Task 8; 11 items 1-9 map to Tasks 1, 2, 3, 4, 4, 5, 6, 7, 8; 12 Task 9 runbook; 13 citations live in the spec.
- Review Focus: 1 Task 2 `test_split_mismatch_closes_the_gate`; 2 Task 4 `test_mark_session_paid_routes_upfront_and_remainder_idempotently`; 3 Task 4 `test_uk_order_still_gets_order_paid_email`; 4 Task 6 `test_checkout_redirects_with_message_when_stripe_refuses`; 5 Task 6 `test_confirmation_pending_then_paid`; 6 Task 8 `test_market_order_gets_remainder_link_once`.
- Type consistency: `createMarketCheckoutSession(Order, string, array)` signature matches the mock expectations in Task 8 and the call in `AppointmentConfirmationService`; `MarketReceipt::$instalment` public for the `Mail::assertQueued` closures; `Market::formatMoney` output format shared by tests in Tasks 2, 5, 7.
- Judgement calls left to the executor: Task 8 Filament `Select::make('market')` fallback when SP4's `marketOptions()` is absent; Task 6 Step 8 if `throttle:12,1` collides with an existing named limiter, use `throttle:contact` as the LP lead route does.
