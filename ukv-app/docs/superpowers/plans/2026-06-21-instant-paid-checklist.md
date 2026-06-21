# Instant Paid Document Checklist — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Gate the document-checklist behind payment so the full tailored checklist reveals **instantly on screen** the moment the customer pays (pay-then-reveal "peek" model), with a free WhatsApp path kept.

**Architecture:** Standalone instant digital product, separate from the visa-service `Order` system. The wizard (step 1 trip + step 2 situation) is free and persists an **unpaid** `ChecklistRequest`; `/checklist/{token}` shows the real list **server-side-redacted** plus 3 tiers + consent; a lightweight Stripe Checkout session (mirroring the order pattern) charges the chosen destination's per-tier price; the webhook is the sole writer of `paid_at`; the Stripe success-return does a read-only session check so the buyer sees the full list instantly.

**Tech Stack:** Laravel 12, Blade (no build step, raw `asset()`), PHPUnit (Tests extend `Tests\TestCase`, `RefreshDatabase`), stripe-php SDK, MySQL. Local PHP: `/c/xampp/php/php.exe`. Run tests: `php artisan test`.

## Global Constraints

- **Spec:** `ukv-app/docs/superpowers/specs/2026-06-21-instant-paid-checklist-design.md` (authoritative).
- **Gate integrity:** unpaid renders MUST NOT emit real document labels into the DOM — server-side redaction, never CSS blur.
- **Paid flag:** `paid_at` is written **only** by the webhook path (`markChecklistPaidByToken`). The success-return verify is **read-only** (no DB write).
- **Price integrity:** tier + `amount_gbp` resolved **server-side** from the destination; never trust a client-supplied amount.
- **Pricing source:** per-destination `tier_standard_gbp / tier_express_gbp / tier_premium_gbp` via `PricingService::tiers()` (shape `array<string,array{tier:OrderTier,service_fee:float,govt_fee:float,total:float}>`). Use `service_fee` only.
- **Consent required before checkout:** `immediate_delivery_consent` must be accepted (immediate delivery + 14-day waiver, Consumer Contracts Regs #131); store `consent_at`.
- **Tiers:** `standard | express | premium` (mirror `App\Enums\OrderTier`). Premium adds a human 1:1 WhatsApp review (labelled, non-instant); the checklist itself is instant for all tiers.
- **Compliance copy:** reuse the existing compliance strip wording; page states **no refund once unlocked**.
- **noindex:** `/checklist/{token}` stays noindex (already set via `partials.seo-meta`).
- **Commit style:** end commit messages with `Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>`.
- **Do NOT** flip global `show_prices`, fold into Orders, or build the templates/sample-letter content library (Premium unlock is wired; content authored separately).

## File Structure

- `database/migrations/2026_06_21_000100_add_payment_to_checklist_requests.php` — new payment columns.
- `app/Models/ChecklistRequest.php` — fillable/casts + `isPaid()` + `peek()`.
- `app/Services/ChecklistPricing.php` — **new**; per-destination tier price + card prices.
- `app/Services/StripeService.php` — add `createChecklistSession()`, `isChecklistSessionPaid()`, `markChecklistPaidByToken()`, webhook branch.
- `app/Jobs/DeliverPaidChecklist.php` — **new**; queued post-pay delivery (Express+ email, Premium team notify).
- `app/Http/Controllers/ChecklistController.php` — add `checkout()`, make `show()` paid-aware.
- `routes/web.php` — add the checkout route.
- `resources/views/public/document-checklist.blade.php` — reinstate step 2, remove the tier gate (plain 2-step wizard).
- `resources/views/public/checklist-result.blade.php` — unpaid peek + tiers + consent; paid full reveal + tier extras.
- `app/Console/Commands/PurgeExpiredDocuments.php` (or a new prune command) — include `checklist_requests` retention.
- Tests under `tests/Unit` and `tests/Feature`.

---

### Task 1: Payment columns + model helpers

**Files:**
- Create: `database/migrations/2026_06_21_000100_add_payment_to_checklist_requests.php`
- Modify: `app/Models/ChecklistRequest.php`
- Test: `tests/Unit/ChecklistRequestPaymentTest.php`

**Interfaces:**
- Produces:
  - columns `tier` (string nullable), `amount_gbp` (decimal 8,2 nullable), `currency` (string(3) default `gbp`), `paid_at` (timestamp nullable), `stripe_session_id` (string nullable), `immediate_delivery_consent` (bool default false), `consent_at` (timestamp nullable)
  - `ChecklistRequest::isPaid(): bool` — `paid_at !== null`
  - `ChecklistRequest::peek(): array{count:int, categories:list<string>, teaser:?array{label:string,note:?string,category:string}}` — redacted projection for unpaid render (NO full labels beyond a single teaser)

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistRequestPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $attrs = []): ChecklistRequest
    {
        $dest = Destination::factory()->create();

        return ChecklistRequest::create(array_merge([
            'destination_id' => $dest->id,
            'inputs' => [],
            'items' => [
                ['document_key' => 'passport', 'label' => 'Valid passport', 'note' => '6 months', 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'photo', 'label' => 'Passport photo', 'note' => null, 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'bank', 'label' => 'Bank statements', 'note' => 'last 3 months', 'category' => 'Finance', 'mandatory' => false],
            ],
        ], $attrs));
    }

    public function test_is_paid_reflects_paid_at(): void
    {
        $this->assertFalse($this->make()->isPaid());
        $this->assertTrue($this->make(['paid_at' => now()])->isPaid());
    }

    public function test_peek_redacts_real_labels_except_one_teaser(): void
    {
        $peek = $this->make()->peek();

        $this->assertSame(3, $peek['count']);
        $this->assertEqualsCanonicalizing(['Identity', 'Finance'], $peek['categories']);
        // Exactly one real label is exposed as the teaser; the rest are withheld.
        $this->assertSame('Valid passport', $peek['teaser']['label']);
    }

    public function test_casts(): void
    {
        $r = $this->make(['amount_gbp' => 35, 'immediate_delivery_consent' => 1, 'consent_at' => now(), 'paid_at' => now()]);
        $r->refresh();

        $this->assertSame('35.00', (string) $r->amount_gbp);
        $this->assertTrue($r->immediate_delivery_consent);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $r->consent_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $r->paid_at);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistRequestPaymentTest`
Expected: FAIL — unknown column / `isPaid()` and `peek()` undefined.

- [ ] **Step 3: Write the migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table): void {
            $table->string('tier')->nullable()->after('items');
            $table->decimal('amount_gbp', 8, 2)->nullable()->after('tier');
            $table->string('currency', 3)->default('gbp')->after('amount_gbp');
            $table->timestamp('paid_at')->nullable()->after('currency');
            $table->string('stripe_session_id')->nullable()->after('paid_at');
            $table->boolean('immediate_delivery_consent')->default(false)->after('stripe_session_id');
            $table->timestamp('consent_at')->nullable()->after('immediate_delivery_consent');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'tier', 'amount_gbp', 'currency', 'paid_at',
                'stripe_session_id', 'immediate_delivery_consent', 'consent_at',
            ]);
        });
    }
};
```

- [ ] **Step 4: Update the model**

In `app/Models/ChecklistRequest.php`, extend `$fillable` and `casts()`, and add the two methods.

Add to `$fillable` (after `'marketing_consent',`):
```php
        'tier',
        'amount_gbp',
        'currency',
        'paid_at',
        'stripe_session_id',
        'immediate_delivery_consent',
        'consent_at',
```

Add to the `casts()` return array:
```php
            'amount_gbp' => 'decimal:2',
            'paid_at' => 'datetime',
            'immediate_delivery_consent' => 'boolean',
            'consent_at' => 'datetime',
```

Add these methods to the class (after the `destination()` relationship):
```php
    /** Money received? Written only by the Stripe webhook path. */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Redacted projection for the UNPAID render. Returns the item count, the distinct
     * category names, and exactly ONE teaser item (its real label). Every other real
     * label is withheld so unpaid HTML never leaks the full list.
     *
     * @return array{count:int, categories:list<string>, teaser:?array{label:string,note:?string,category:string}}
     */
    public function peek(): array
    {
        $items = is_array($this->items) ? $this->items : [];

        $categories = array_values(array_unique(array_filter(
            array_map(static fn ($i) => $i['category'] ?? null, $items)
        )));

        $first = $items[0] ?? null;
        $teaser = $first === null ? null : [
            'label' => (string) ($first['label'] ?? ''),
            'note' => $first['note'] ?? null,
            'category' => (string) ($first['category'] ?? ''),
        ];

        return [
            'count' => count($items),
            'categories' => $categories,
            'teaser' => $teaser,
        ];
    }
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=ChecklistRequestPaymentTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_06_21_000100_add_payment_to_checklist_requests.php app/Models/ChecklistRequest.php tests/Unit/ChecklistRequestPaymentTest.php
git commit -m "feat(checklist): payment columns + isPaid/peek redaction on ChecklistRequest

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Per-destination checklist pricing helper

**Files:**
- Create: `app/Services/ChecklistPricing.php`
- Test: `tests/Unit/ChecklistPricingTest.php`

**Interfaces:**
- Consumes: `PricingService::tiers(Destination): array<string,array{service_fee:float,...}>` (Task uses `service_fee`).
- Produces:
  - `ChecklistPricing::priceFor(Destination $d, string $tier): float` — service fee for that destination+tier; throws `InvalidArgumentException` for an unknown/unpriced tier.
  - `ChecklistPricing::cards(Destination $d): array<string,float>` — `['standard'=>x,'express'=>y,'premium'=>z]`, only tiers with a positive price.

Note: the gate lives on `/checklist/{token}` where the destination is always known, so exact per-destination prices are shown (no cross-destination "from" floor needed — YAGNI).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Destination;
use App\Services\ChecklistPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_for_returns_destination_tier_service_fee(): void
    {
        $d = Destination::factory()->create([
            'tier_standard_gbp' => 35, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85,
        ]);
        $p = app(ChecklistPricing::class);

        $this->assertSame(35.0, $p->priceFor($d, 'standard'));
        $this->assertSame(85.0, $p->priceFor($d, 'premium'));
    }

    public function test_price_for_throws_on_unknown_tier(): void
    {
        $d = Destination::factory()->create(['tier_standard_gbp' => 35]);
        $this->expectException(\InvalidArgumentException::class);
        app(ChecklistPricing::class)->priceFor($d, 'gold');
    }

    public function test_cards_lists_only_positive_priced_tiers(): void
    {
        $d = Destination::factory()->create([
            'tier_standard_gbp' => 0, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85,
        ]);
        $cards = app(ChecklistPricing::class)->cards($d);

        $this->assertArrayNotHasKey('standard', $cards);
        $this->assertSame(55.0, $cards['express']);
        $this->assertSame(85.0, $cards['premium']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistPricingTest`
Expected: FAIL — class `App\Services\ChecklistPricing` not found.

- [ ] **Step 3: Write the implementation**

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Destination;

/**
 * Pricing for the standalone instant document-checklist product.
 *
 * Reuses the per-destination service-fee table (PricingService::tiers) so the checklist
 * charges the same per-destination tier price /apply uses, but only the SERVICE FEE
 * (no government fee — a checklist is information, not a submission).
 */
final class ChecklistPricing
{
    /** Valid tier keys for the checklist product. */
    public const TIERS = ['standard', 'express', 'premium'];

    public function __construct(private readonly PricingService $pricing) {}

    /** Service fee (GBP) for a destination + tier. Throws on an unknown/unpriced tier. */
    public function priceFor(Destination $destination, string $tier): float
    {
        if (! in_array($tier, self::TIERS, true)) {
            throw new \InvalidArgumentException("Unknown checklist tier '{$tier}'.");
        }

        $tiers = $this->pricing->tiers($destination);
        if (! isset($tiers[$tier])) {
            throw new \InvalidArgumentException(
                "Tier '{$tier}' is not priced for destination {$destination->getKey()}."
            );
        }

        return (float) $tiers[$tier]['service_fee'];
    }

    /**
     * Tier => price map for the gate cards (only positively-priced tiers).
     *
     * @return array<string,float>
     */
    public function cards(Destination $destination): array
    {
        $out = [];
        foreach ($this->pricing->tiers($destination) as $key => $row) {
            $fee = (float) $row['service_fee'];
            if ($fee > 0) {
                $out[$key] = $fee;
            }
        }

        return $out;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ChecklistPricingTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Services/ChecklistPricing.php tests/Unit/ChecklistPricingTest.php
git commit -m "feat(checklist): per-destination checklist pricing helper

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Stripe session + webhook branch + paid marker

**Files:**
- Modify: `app/Services/StripeService.php`
- Test: `tests/Feature/ChecklistPaymentTest.php` (new — webhook-branch + idempotency portion)

**Interfaces:**
- Consumes: `ChecklistRequest` (Task 1), `ChecklistPricing::priceFor()` (Task 2), `DeliverPaidChecklist` job (Task 8 — dispatched by name; until Task 8 lands, dispatch is guarded behind `class_exists`).
- Produces:
  - `StripeService::createChecklistSession(ChecklistRequest $r): string` — hosted URL; throws `InvalidArgumentException` if `$r->tier` / destination / price missing.
  - `StripeService::isChecklistSessionPaid(string $token, string $sessionId): bool` — read-only Stripe retrieve; true iff `payment_status==='paid'` and `metadata.token===$token`.
  - `StripeService::markChecklistPaidByToken(string $token, ?string $sessionId, ?string $email): void` — idempotent on `paid_at`; sets `paid_at`, `stripe_session_id`, `email` (if given), dispatches delivery.
  - `handleWebhook()` now branches: `metadata.type==='checklist'` → resolve token → `markChecklistPaidByToken()`.

**Constraint:** `markChecklistPaidByToken` must be DB-only (no Stripe call) so it is unit-testable without the SDK.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function unpaid(array $attrs = []): ChecklistRequest
    {
        $d = Destination::factory()->create(['tier_standard_gbp' => 35]);

        return ChecklistRequest::create(array_merge([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
            'tier' => 'standard',
            'amount_gbp' => 35,
        ], $attrs));
    }

    public function test_mark_checklist_paid_sets_paid_at_and_is_idempotent(): void
    {
        $r = $this->unpaid();
        $svc = app(StripeService::class);

        $svc->markChecklistPaidByToken($r->token, 'cs_test_1', 'buyer@example.com');
        $r->refresh();
        $firstPaidAt = $r->paid_at;

        $this->assertNotNull($firstPaidAt);
        $this->assertSame('cs_test_1', $r->stripe_session_id);
        $this->assertSame('buyer@example.com', $r->email);

        // Replay: must not move paid_at or change anything.
        $svc->markChecklistPaidByToken($r->token, 'cs_test_2', 'other@example.com');
        $r->refresh();
        $this->assertEquals($firstPaidAt, $r->paid_at);
        $this->assertSame('cs_test_1', $r->stripe_session_id);
    }

    public function test_mark_checklist_paid_unknown_token_is_noop(): void
    {
        app(StripeService::class)->markChecklistPaidByToken('no-such-token', 'cs_x', null);
        $this->assertTrue(true); // no exception
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistPaymentTest`
Expected: FAIL — `markChecklistPaidByToken` undefined.

- [ ] **Step 3: Add the methods to StripeService**

Add `use App\Models\ChecklistRequest;` and `use App\Services\ChecklistPricing;` to the imports. Add these methods to the class:

```php
    /**
     * Build a Stripe Checkout Session for an instant CHECKLIST purchase. Charges the chosen
     * tier's per-destination service fee. metadata.type='checklist' routes the webhook to the
     * checklist path. Throws if the request has no tier / destination / resolvable price.
     */
    public function createChecklistSession(ChecklistRequest $request): string
    {
        $destination = $request->destination;
        if ($destination === null) {
            throw new \InvalidArgumentException("Checklist {$request->token} has no destination.");
        }

        $tier = (string) $request->tier;
        if ($tier === '') {
            throw new \InvalidArgumentException("Checklist {$request->token} has no tier.");
        }

        $amount = app(ChecklistPricing::class)->priceFor($destination, $tier);
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Tier '{$tier}' is not priced for this destination.");
        }

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => (int) round($amount * 100),
                    'product_data' => [
                        'name' => sprintf(
                            '%s document checklist — %s',
                            $destination->name ?? 'Trip',
                            ucfirst($tier),
                        ),
                    ],
                ],
            ]],
            'metadata' => [
                'type' => 'checklist',
                'checklist_id' => (string) $request->getKey(),
                'token' => (string) $request->token,
                'tier' => $tier,
            ],
            'client_reference_id' => (string) $request->token,
            'success_url' => url('/checklist/'.$request->token).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/checklist/'.$request->token),
        ]);

        return (string) $session->url;
    }

    /**
     * Read-only check used on the Stripe success return so the buyer sees the list instantly,
     * even before the webhook lands. NO database write. True iff the session is paid and its
     * metadata.token matches.
     */
    public function isChecklistSessionPaid(string $token, string $sessionId): bool
    {
        if ($sessionId === '' || (string) config('services.stripe.secret') === '') {
            return false;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Checklist session retrieve failed.', ['error' => $e->getMessage()]);

            return false;
        }

        $metaToken = $session->metadata->token ?? null;

        return ($session->payment_status ?? null) === 'paid' && $metaToken === $token;
    }

    /**
     * Idempotently mark a checklist paid (sole writer of paid_at on the checklist path).
     * DB-only — safe to call from tests without the Stripe SDK. Dispatches post-pay delivery.
     */
    public function markChecklistPaidByToken(string $token, ?string $sessionId, ?string $email): void
    {
        $request = ChecklistRequest::query()->where('token', $token)->first();
        if ($request === null) {
            Log::warning('Stripe webhook: checklist not found for token.', ['token' => $token]);

            return;
        }

        if ($request->paid_at !== null) {
            return; // idempotent
        }

        $request->paid_at = \Illuminate\Support\Carbon::now();
        $request->stripe_session_id = $sessionId;
        if ($email !== null && $email !== '' && $request->email === null) {
            $request->email = $email;
        }
        $request->save();

        if (class_exists(\App\Jobs\DeliverPaidChecklist::class)) {
            \App\Jobs\DeliverPaidChecklist::dispatch($request->id);
        }
    }
```

- [ ] **Step 4: Branch the webhook on metadata.type**

In `handleWebhook()`, replace the order-resolution block (after `$session = $event->data->object;`) so checklist sessions route to the checklist path:

```php
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        // Checklist purchases carry metadata.type='checklist' and are resolved by token.
        if (($session->metadata->type ?? null) === 'checklist') {
            $token = (string) ($session->metadata->token ?? $session->client_reference_id ?? '');
            if ($token !== '') {
                $this->markChecklistPaidByToken($token, $session->id ?? null, $session->customer_email ?? null);
            }

            return;
        }

        $order = $this->resolveOrderFromSession($session);
        if ($order === null) {
            Log::warning('Stripe webhook: could not resolve order from session.', [
                'session_id' => $session->id ?? null,
            ]);

            return;
        }

        $this->markOrderPaid($order, $session);
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=ChecklistPaymentTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Services/StripeService.php tests/Feature/ChecklistPaymentTest.php
git commit -m "feat(checklist): Stripe checklist session + webhook branch + idempotent paid marker

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Checkout route + controller action (consent + price snapshot)

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/ChecklistController.php`
- Test: `tests/Feature/ChecklistCheckoutTest.php`

**Interfaces:**
- Consumes: `StripeService::createChecklistSession()` (Task 3), `ChecklistPricing::priceFor()` (Task 2).
- Produces:
  - Route `POST /checklist/{checklistRequest}/checkout` → `checklist.checkout`.
  - `ChecklistController::checkout(Request $request, ChecklistRequest $checklistRequest, StripeService $stripe): RedirectResponse`.

Behaviour: validate `tier in standard|express|premium` + `consent accepted`; if already paid → redirect to `checklist.show`; else snapshot `tier`, `amount_gbp`, `immediate_delivery_consent=true`, `consent_at=now`, optional `email`; create session; `redirect()->away($url)`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ChecklistCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function request(): ChecklistRequest
    {
        $d = Destination::factory()->create(['tier_standard_gbp' => 35, 'tier_express_gbp' => 55]);

        return ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
        ]);
    }

    public function test_checkout_requires_consent(): void
    {
        $r = $this->request();

        $this->post("/checklist/{$r->token}/checkout", ['tier' => 'standard'])
            ->assertSessionHasErrors('consent');

        $r->refresh();
        $this->assertNull($r->paid_at);
        $this->assertNull($r->tier);
    }

    public function test_checkout_with_consent_snapshots_and_redirects_to_stripe(): void
    {
        $r = $this->request();

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('createChecklistSession')
            ->once()
            ->andReturn('https://checkout.stripe.test/session');
        $this->app->instance(StripeService::class, $mock);

        $this->post("/checklist/{$r->token}/checkout", [
            'tier' => 'express',
            'consent' => '1',
            'email' => 'buyer@example.com',
        ])->assertRedirect('https://checkout.stripe.test/session');

        $r->refresh();
        $this->assertSame('express', $r->tier);
        $this->assertSame('55.00', (string) $r->amount_gbp);
        $this->assertTrue($r->immediate_delivery_consent);
        $this->assertNotNull($r->consent_at);
        $this->assertSame('buyer@example.com', $r->email);
    }

    public function test_checkout_when_already_paid_redirects_to_result_without_new_session(): void
    {
        $r = $this->request();
        $r->update(['paid_at' => now(), 'tier' => 'standard']);

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldNotReceive('createChecklistSession');
        $this->app->instance(StripeService::class, $mock);

        $this->post("/checklist/{$r->token}/checkout", ['tier' => 'standard', 'consent' => '1'])
            ->assertRedirect("/checklist/{$r->token}");
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistCheckoutTest`
Expected: FAIL — route `checklist.checkout` not defined (404/MethodNotAllowed).

- [ ] **Step 3: Add the route**

In `routes/web.php`, directly after the existing `checklist.show` route (`Route::get('/checklist/{checklistRequest}', ...)`), add:

```php
Route::post('/checklist/{checklistRequest}/checkout', [ChecklistController::class, 'checkout'])
    ->name('checklist.checkout');
```

- [ ] **Step 4: Add the controller action**

Add `use App\Services\StripeService;` to `ChecklistController` imports, and this method:

```php
    /**
     * Take the chosen tier + immediate-delivery consent, snapshot the price server-side,
     * and start a Stripe Checkout session for the instant checklist. Already-paid requests
     * skip straight to the (now full) result. paid_at is written only by the webhook.
     */
    public function checkout(Request $request, ChecklistRequest $checklistRequest, StripeService $stripe): RedirectResponse
    {
        $validated = $request->validate([
            'tier' => ['required', 'in:standard,express,premium'],
            'consent' => ['accepted'],
            'email' => ['nullable', 'email', 'max:160'],
        ]);

        if ($checklistRequest->isPaid()) {
            return redirect()->route('checklist.show', ['checklistRequest' => $checklistRequest->token]);
        }

        $checklistRequest->loadMissing('destination');
        abort_if($checklistRequest->destination === null, 404);

        $amount = app(\App\Services\ChecklistPricing::class)
            ->priceFor($checklistRequest->destination, $validated['tier']);

        $checklistRequest->fill([
            'tier' => $validated['tier'],
            'amount_gbp' => $amount,
            'currency' => 'gbp',
            'immediate_delivery_consent' => true,
            'consent_at' => now(),
        ]);
        if (! empty($validated['email'])) {
            $checklistRequest->email = $validated['email'];
        }
        $checklistRequest->save();

        return redirect()->away($stripe->createChecklistSession($checklistRequest));
    }
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=ChecklistCheckoutTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add routes/web.php app/Http/Controllers/ChecklistController.php tests/Feature/ChecklistCheckoutTest.php
git commit -m "feat(checklist): checkout action — consent gate + server-side price snapshot + Stripe redirect

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 5: Paid-aware result page (peek vs full reveal)

**Files:**
- Modify: `app/Http/Controllers/ChecklistController.php` (`show()`)
- Modify: `resources/views/public/checklist-result.blade.php`
- Test: `tests/Feature/ChecklistRevealTest.php`

**Interfaces:**
- Consumes: `ChecklistRequest::isPaid()` / `peek()` (Task 1), `StripeService::isChecklistSessionPaid()` (Task 3), `ChecklistPricing::cards()` (Task 2).
- Produces: `show(ChecklistRequest $checklistRequest, Request $request, StripeService $stripe): View` passing `$paid` (bool), `$peek` (array), `$tierCards` (array<string,float>) to the view.

`$paid = $checklistRequest->isPaid() || ($sessionId && $stripe->isChecklistSessionPaid($token, $sessionId))`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistRevealTest extends TestCase
{
    use RefreshDatabase;

    private function request(array $attrs = []): ChecklistRequest
    {
        $d = Destination::factory()->create(['name' => 'Turkey', 'tier_standard_gbp' => 35, 'tier_express_gbp' => 55, 'tier_premium_gbp' => 85]);

        return ChecklistRequest::create(array_merge([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [
                ['document_key' => 'passport', 'label' => 'Valid passport', 'note' => '6 months', 'category' => 'Identity', 'mandatory' => true],
                ['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => false],
            ],
        ], $attrs));
    }

    public function test_unpaid_page_redacts_real_labels_and_shows_tiers(): void
    {
        $r = $this->request();

        $res = $this->get("/checklist/{$r->token}");
        $res->assertOk();
        // Gate integrity: the second (non-teaser) real label must NOT appear in the DOM.
        $res->assertDontSee('Bank statements');
        // Tier prices are shown.
        $res->assertSee('55');
        $res->assertSee('Express');
    }

    public function test_paid_page_reveals_full_list(): void
    {
        $r = $this->request(['paid_at' => now(), 'tier' => 'express']);

        $res = $this->get("/checklist/{$r->token}");
        $res->assertOk();
        $res->assertSee('Valid passport');
        $res->assertSee('Bank statements');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistRevealTest`
Expected: FAIL — unpaid page still renders full items (`assertDontSee('Bank statements')` fails), no tier prices.

- [ ] **Step 3: Update `show()`**

Replace the existing `show()` method with:

```php
    public function show(ChecklistRequest $checklistRequest, Request $request, StripeService $stripe): View
    {
        $checklistRequest->loadMissing('destination');

        $sessionId = (string) $request->query('session_id', '');
        $paid = $checklistRequest->isPaid()
            || ($sessionId !== '' && $stripe->isChecklistSessionPaid($checklistRequest->token, $sessionId));

        $tierCards = $checklistRequest->destination !== null
            ? app(\App\Services\ChecklistPricing::class)->cards($checklistRequest->destination)
            : [];

        return view('public.checklist-result', [
            'request' => $checklistRequest,
            'destination' => $checklistRequest->destination,
            'paid' => $paid,
            'peek' => $checklistRequest->peek(),
            'tierCards' => $tierCards,
        ]);
    }
```

- [ ] **Step 4: Make the view paid-aware**

In `resources/views/public/checklist-result.blade.php`:

(a) In the `@php` block (after `$items = $request->items ?? [];`), add a default so older callers don't break:
```php
    $paid       = $paid ?? true;
    $peek       = $peek ?? ['count' => count($items), 'categories' => [], 'teaser' => null];
    $tierCards  = $tierCards ?? [];
    $tierMeta   = [
        'standard' => ['name' => 'Standard', 'feat' => false, 'lines' => ['Personalised document list', 'Checked against your trip', 'Saved shareable link']],
        'express'  => ['name' => 'Express',  'feat' => true,  'lines' => ['Everything in Standard', 'Downloadable PDF pack', 'Calendar reminders + emailed copy']],
        'premium'  => ['name' => 'Premium',  'feat' => false, 'lines' => ['Everything in Express', 'Document templates & samples', 'Family checklist + 1:1 WhatsApp review']],
    ];
    $waNum      = config('ukv.whatsapp') ?: '440000000000';
    $waHref     = 'https://wa.me/'.$waNum.'?text='.urlencode('Hi Beyond Passports — I would like help with my document checklist for '.$destName.'.');
```

(b) Replace the checklist panel section (the `{{-- ── THE CHECKLIST (snapshotted items) ── --}}` `<section>…</section>` block) with a paid/unpaid branch:

```blade
  {{-- ── THE CHECKLIST — full when paid, redacted peek + gate when unpaid ── --}}
  <section class="cr-section"><div class="wrap">
    @if ($paid)
      <div class="cr-panel reveal">
        @include('partials.doc-checklist', ['items' => $items, 'personalised' => true])
      </div>
    @else
      {{-- PEEK: server-side redaction — count + categories + ONE teaser. No other labels. --}}
      <div class="cr-panel reveal" style="position:relative">
        <p class="ch-label" style="margin-top:0">Your {{ $destName }} checklist · {{ $peek['count'] }} items</p>
        @if (! empty($peek['categories']))
          <p style="color:var(--muted);font-size:13.5px;margin:0 0 16px">Covers: {{ implode(' · ', $peek['categories']) }}</p>
        @endif
        @if ($peek['teaser'])
          <div style="display:flex;gap:11px;align-items:flex-start;padding:12px 0;border-top:1px dashed var(--paper-edge)">
            <span style="color:var(--stamp);font-weight:800">✓</span>
            <span><b style="color:var(--navy)">{{ $peek['teaser']['label'] }}</b>
            @if ($peek['teaser']['note'])<span style="display:block;font-size:12.5px;color:var(--muted)">{{ $peek['teaser']['note'] }}</span>@endif</span>
          </div>
        @endif
        @for ($i = 1; $i < min($peek['count'], 6); $i++)
          <div style="display:flex;gap:11px;align-items:center;padding:12px 0;border-top:1px dashed var(--paper-edge);filter:blur(0)">
            <span style="color:var(--paper-edge);font-weight:800">●</span>
            <span style="height:13px;flex:1;max-width:{{ 60 - ($i*6) }}%;background:var(--paper-edge);border-radius:7px"></span>
          </div>
        @endfor
        <p style="font-size:12.5px;color:var(--muted);margin:16px 0 0">Unlock the full list below — it appears here instantly.</p>
      </div>

      {{-- FREE WhatsApp path first (centered), then "or unlock instantly", then tiers --}}
      <div class="cr-panel reveal" style="text-align:center;margin-top:18px">
        <p class="ch-label" style="justify-content:center">Free</p>
        <b style="font:800 17px var(--display);color:var(--navy)">Just need a quick answer?</b>
        <p style="font-size:13px;color:#3a4b55;max-width:44ch;margin:4px auto 14px">Message our UK team — a real person, no payment, general guidance for your trip.</p>
        <a href="{{ $waHref }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:9px;border-radius:11px;padding:13px 22px;font:800 14px var(--display);color:#fff;background:#25D366;text-decoration:none">Ask free on WhatsApp →</a>
      </div>

      <div style="display:flex;align-items:center;gap:14px;margin:20px 2px;max-width:760px;margin-inline:auto">
        <span style="flex:1;height:1px;background:var(--paper-edge)"></span>
        <span style="font:800 11px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--muted)">or unlock the full checklist instantly</span>
        <span style="flex:1;height:1px;background:var(--paper-edge)"></span>
      </div>

      @if ($errors->any())
        <div class="server-errors" role="alert" style="max-width:760px;margin:0 auto 14px">
          <strong>Please fix the following:</strong>
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form method="POST" action="{{ url('/checklist/'.$request->token.'/checkout') }}" class="cr-panel reveal" style="max-width:760px;margin:0 auto" id="dct-pay">
        @csrf
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
          @foreach (['standard','express','premium'] as $key)
            @continue(! isset($tierCards[$key]))
            <label style="border:1px solid {{ $tierMeta[$key]['feat'] ? 'var(--cta)' : 'var(--paper-edge)' }};border-radius:14px;padding:18px 16px;display:block;cursor:pointer;text-align:left">
              <input type="radio" name="tier" value="{{ $key }}" @checked($key==='express') style="accent-color:var(--cta)">
              <span style="display:block;font:800 12px var(--display);letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:8px 0 2px">{{ $tierMeta[$key]['name'] }}</span>
              <span style="display:block;font:800 20px var(--display);color:var(--navy)">£{{ rtrim(rtrim(number_format($tierCards[$key], 2), '0'), '.') }}</span>
              <span style="display:block;font-size:12px;color:var(--muted);margin-top:8px">
                @foreach ($tierMeta[$key]['lines'] as $line)✓ {{ $line }}<br>@endforeach
              </span>
            </label>
          @endforeach
        </div>

        <label style="display:flex;gap:10px;align-items:flex-start;margin:16px 0;padding:14px 16px;background:var(--paper);border:1px solid var(--paper-edge);border-radius:10px;font-size:13px;color:var(--muted)">
          <input type="checkbox" name="consent" value="1" style="margin-top:3px;accent-color:var(--cta)" @error('consent') aria-invalid="true" @enderror>
          <span>I want my checklist <strong>delivered immediately</strong> and understand that, because it's digital content provided at once, I <strong>lose my 14-day right to cancel</strong>. No refund once the list is unlocked.</span>
        </label>

        <button type="submit" class="btn" style="width:100%;padding:15px;font-size:16px">Unlock my full checklist →</button>
        <p style="font-size:12px;color:var(--muted);text-align:center;margin:12px 0 0">Service fee only — separate from any government fee. No approval guaranteed.</p>
      </form>
    @endif
  </div></section>
```

(c) Wrap the "send me this" delivery section, the share section, and the sticky action bar so they show **only when paid** (an unpaid visitor has nothing to share/save yet). Change each opening guard:
- sticky bar: `@if (config('ukv.checklist.sticky_action_bar', true))` → `@if ($paid && config('ukv.checklist.sticky_action_bar', true))`
- delivery `<section>`: wrap with `@if ($paid)` … `@endif`
- share `<section>`: wrap with `@if ($paid)` … `@endif`

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=ChecklistRevealTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Manual gate-integrity check (view source)**

Run: `php artisan serve` then open an unpaid token; View Source; confirm only the teaser label appears and no other real document label is in the HTML.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/ChecklistController.php resources/views/public/checklist-result.blade.php tests/Feature/ChecklistRevealTest.php
git commit -m "feat(checklist): paid-aware result page — redacted peek + tiers/consent gate vs full reveal

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: Reinstate step 2 wizard + remove the old tier gate

**Files:**
- Modify: `resources/views/public/document-checklist.blade.php`
- Test: `tests/Feature/ChecklistWizardTest.php`

**Interfaces:**
- Consumes: existing `ChecklistController::result()` (unchanged — already persists an unpaid `ChecklistRequest` and redirects to the token).
- Produces: a plain 2-step wizard (step 1 trip, step 2 situation) that POSTs to `/document-checklist`. The pay gate now lives on the result page (Task 5), NOT here.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChecklistWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_shows_both_steps_and_no_tier_gate(): void
    {
        Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->get('/document-checklist');
        $res->assertOk();
        $res->assertSee('name="residency_status"', false);   // step 2 reinstated
        $res->assertSee('name="prior_refusal"', false);      // step 2 reinstated
        $res->assertDontSee('gate-tier');                    // old tier gate removed
    }

    public function test_submitting_the_wizard_creates_an_unpaid_request_and_redirects(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);

        $res = $this->post('/document-checklist', [
            'destination' => 'Turkey',
            'residency_status' => 'citizen',
            'prior_refusal' => 'no',
        ]);

        $request = ChecklistRequest::query()->latest('id')->first();
        $this->assertNotNull($request);
        $this->assertNull($request->paid_at);
        $res->assertRedirect("/checklist/{$request->token}");
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ChecklistWizardTest`
Expected: FAIL — step-2 fields absent (removed earlier), `gate-tier` still present.

- [ ] **Step 3: Edit the wizard view**

In `resources/views/public/document-checklist.blade.php`:

1. **Re-add the step-2 situation fields.** Replace the current step-2 `<div class="dct-step" data-step="2">…</div>` (the gate block with `dct-sum` / `gate` / `gate-free` / `gate-tier` / `dct-tiers`) with the original situation form:

```blade
            <div class="dct-step" data-step="2">
            <p class="dct-shead">A few more details</p>
            <p class="dct-ssub">Your situation helps us tailor the list — every field here is optional.</p>
            <div class="grid2">
              <div class="field">
                <label for="residency_status">Residency status</label>
                <select id="residency_status" name="residency_status">
                  <option value="">No preference</option>
                  <option value="citizen" @selected(old('residency_status') === 'citizen')>Citizen</option>
                  <option value="permanent" @selected(old('residency_status') === 'permanent')>Settled / permanent resident</option>
                  <option value="visa_holder" @selected(old('residency_status') === 'visa_holder')>Visa holder</option>
                </select>
              </div>
              <div class="field">
                <label for="employment_status">Employment status</label>
                <select id="employment_status" name="employment_status">
                  <option value="">No preference</option>
                  <option value="employed" @selected(old('employment_status') === 'employed')>Employed</option>
                  <option value="self_employed" @selected(old('employment_status') === 'self_employed')>Self-employed</option>
                  <option value="student" @selected(old('employment_status') === 'student')>Student</option>
                  <option value="retired" @selected(old('employment_status') === 'retired')>Retired</option>
                  <option value="unemployed" @selected(old('employment_status') === 'unemployed')>Not currently working</option>
                </select>
              </div>
              <div class="field">
                <label for="accommodation_type">Where you'll stay</label>
                <select id="accommodation_type" name="accommodation_type">
                  <option value="">No preference</option>
                  <option value="hotel" @selected(old('accommodation_type') === 'hotel')>Hotel / paid accommodation</option>
                  <option value="host" @selected(old('accommodation_type') === 'host')>Staying with family / friends</option>
                  <option value="own" @selected(old('accommodation_type') === 'own')>My own property</option>
                  <option value="other" @selected(old('accommodation_type') === 'other')>Other</option>
                </select>
              </div>
              <div class="field">
                <label for="funding_source">Who's funding the trip</label>
                <select id="funding_source" name="funding_source">
                  <option value="">No preference</option>
                  <option value="self" @selected(old('funding_source') === 'self')>Funding it myself</option>
                  <option value="sponsor" @selected(old('funding_source') === 'sponsor')>A sponsor (family / friend)</option>
                  <option value="employer" @selected(old('funding_source') === 'employer')>My employer</option>
                </select>
              </div>
              <div class="field">
                <label for="is_minor">Is the traveller a minor (under 18)?</label>
                <select id="is_minor" name="is_minor">
                  <option value="">No preference</option>
                  <option value="no" @selected(old('is_minor') === 'no')>No</option>
                  <option value="yes" @selected(old('is_minor') === 'yes')>Yes</option>
                </select>
              </div>
              <div class="field">
                <label for="prior_refusal">Any previous visa refusal (any country)?</label>
                <select id="prior_refusal" name="prior_refusal">
                  <option value="">No preference</option>
                  <option value="no" @selected(old('prior_refusal') === 'no')>No</option>
                  <option value="yes" @selected(old('prior_refusal') === 'yes')>Yes</option>
                </select>
                <p class="hint">A prior refusal can change what's required. It does not mean we can't help.</p>
              </div>
            </div>
            </div>{{-- /step 2 --}}
```

2. **Restore the submit row** (after the `dct-wnav` nav block) so the wizard submits to build the checklist:

```blade
            <div class="dct-submit-row">
              <button type="submit" class="btn">See my checklist &rarr;</button>
              <p class="sub-note">Free to see what you'll need · pay only to unlock the full list</p>
            </div>
```

3. **Restore the wizard JS** so step 2 → submit (not gate). In the `@push('head')<script>` block, change the `nextBtn` click handler and the `show()` Next label back to the submit behaviour:

```javascript
      nextBtn.addEventListener('click', function () {
        if (cur === 1) { if (guard()) show(2); return; }
        if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
      });
```
and in `show(n)`:
```javascript
        nextBtn.style.display = '';
        nextBtn.innerHTML = (n === 2) ? 'See my checklist &rarr;' : 'Next: your situation &rarr;';
```
Remove the `syncGate()` call, the `data-edit` handler, and the `.gate-tier`/`#dct-free-wa` sync code (they reference the removed gate). Re-add a `submit` listener that guards destination + sets a submitting state (mirror the pre-gate version):
```javascript
    form.addEventListener('submit', function (e) {
      if (!dest.value) { e.preventDefault(); dest.setAttribute('aria-invalid','true'); dest.focus(); return; }
    });
```

4. **Delete now-unused gate CSS** in this file's `@push('head')<style>`: the `.dct-sum`, `.gate`, `.gate-top`, `.gate-body`, `.gate-free`, `.gate-or`, `.dct-tiers`, `.dct-tier`, `.dct-free`, `.dct-wa`, and `.dct-tier .from` rules (they moved to / are superseded by the result-page gate). Keep the wizard/step CSS (`.dct-steps`, `.dct-step`, `.dct-wnav`, `.dct-prog`, `.dct-submit-row`, form layout).

5. **Step-2 tab label** back to situation: `<span class="ts">Your situation</span>` (it currently reads "Your checklist").

6. **Hero copy:** keep the honest framing but reflect "pay to unlock" — set the lede to: `Tell us about your trip and we'll build your checklist. Unlock the full list instantly, or ask a quick question free on WhatsApp.`

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --filter=ChecklistWizardTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Verify the inline JS parses (Blade-rendered)**

Run: `php artisan serve` then:
```bash
curl -s http://127.0.0.1:8000/document-checklist | sed -n 's/.*<script>\(.*\)<\/script>.*/\1/p' > /tmp/dct.js && node --check /tmp/dct.js && echo "JS OK"
```
Expected: `JS OK` (no apostrophe/interpolation breakage).

- [ ] **Step 6: Commit**

```bash
git add resources/views/public/document-checklist.blade.php tests/Feature/ChecklistWizardTest.php
git commit -m "feat(checklist): reinstate step-2 wizard; move pay gate to result page

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 7: Instant reveal on Stripe success return

**Files:**
- Test: `tests/Feature/ChecklistSuccessReturnTest.php`

(`show()` already consumes `isChecklistSessionPaid()` from Task 5; this task proves the read-only success-return reveals before the webhook, and writes no `paid_at`.)

**Interfaces:**
- Consumes: `StripeService::isChecklistSessionPaid()` (Task 3), `show()` (Task 5).

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ChecklistSuccessReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_session_id_reveals_before_webhook_without_writing_paid_at(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);
        $r = ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => true]],
        ]);

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('isChecklistSessionPaid')
            ->with($r->token, 'cs_live_1')->once()->andReturn(true);
        $this->app->instance(StripeService::class, $mock);

        $res = $this->get("/checklist/{$r->token}?session_id=cs_live_1");
        $res->assertOk();
        $res->assertSee('Bank statements'); // revealed via read-only verify

        $r->refresh();
        $this->assertNull($r->paid_at); // success return must NOT write paid_at (webhook does)
    }

    public function test_invalid_session_id_stays_redacted(): void
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);
        $r = ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'bank', 'label' => 'Bank statements', 'note' => null, 'category' => 'Finance', 'mandatory' => true]],
        ]);

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('isChecklistSessionPaid')->andReturn(false);
        $this->app->instance(StripeService::class, $mock);

        $this->get("/checklist/{$r->token}?session_id=bad")->assertDontSee('Bank statements');
    }
}
```

- [ ] **Step 2: Run test to verify it passes (no new code expected)**

Run: `php artisan test --filter=ChecklistSuccessReturnTest`
Expected: PASS (2 tests). If FAIL, ensure `show()` from Task 5 reads `session_id` and calls `isChecklistSessionPaid()` and writes nothing.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/ChecklistSuccessReturnTest.php
git commit -m "test(checklist): success-return read-only reveal does not write paid_at

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 8: Post-pay delivery job (Express+ email, Premium notify)

**Files:**
- Create: `app/Jobs/DeliverPaidChecklist.php`
- Test: `tests/Feature/DeliverPaidChecklistTest.php`

**Interfaces:**
- Consumes: `ChecklistRequest` (Task 1), dispatched by `markChecklistPaidByToken()` (Task 3) as `DeliverPaidChecklist::dispatch($request->id)`.
- Produces: queued job that, for a **paid** request: Express/Premium → emails the checklist (PDF + .ics) to `$request->email` if present; Premium → logs a team-notify line for the 1:1 WhatsApp review. Standard → no-op (reveal-only).

Note: reuse the existing delivery wiring rather than authoring new mail. The minimal, testable contract here is **"the job runs and emails Express+ buyers with an email on file."** Use `EmailService` if it exposes a checklist method; otherwise send a `Mailable`. Check first:

```bash
grep -niE "checklist" app/Services/EmailService.php app/Http/Controllers/ChecklistDeliveryController.php
```
Use whatever send method that surfaces. The test below asserts mail is *sent* (Mail fake) for Express, not for Standard — keep the job's mail call behind `Mail::to(...)->send(...)` or the discovered service call so the fake intercepts it.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\DeliverPaidChecklist;
use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class DeliverPaidChecklistTest extends TestCase
{
    use RefreshDatabase;

    private function paid(string $tier, ?string $email): ChecklistRequest
    {
        $d = Destination::factory()->create(['name' => 'Turkey']);

        return ChecklistRequest::create([
            'destination_id' => $d->id,
            'inputs' => [],
            'items' => [['document_key' => 'passport', 'label' => 'Valid passport', 'note' => null, 'category' => 'Identity', 'mandatory' => true]],
            'tier' => $tier,
            'paid_at' => now(),
            'email' => $email,
        ]);
    }

    public function test_express_with_email_sends_mail(): void
    {
        Mail::fake();
        $r = $this->paid('express', 'buyer@example.com');

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertSentCount(1);
    }

    public function test_standard_does_not_send_mail(): void
    {
        Mail::fake();
        $r = $this->paid('standard', 'buyer@example.com');

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertNothingSent();
    }

    public function test_express_without_email_sends_nothing(): void
    {
        Mail::fake();
        $r = $this->paid('express', null);

        (new DeliverPaidChecklist($r->id))->handle();

        Mail::assertNothingSent();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DeliverPaidChecklistTest`
Expected: FAIL — class `App\Jobs\DeliverPaidChecklist` not found.

- [ ] **Step 3: Write the job**

Create a `Mailable` if no checklist mail exists. First add `app/Mail/PaidChecklistMail.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class PaidChecklistMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ChecklistRequest $request) {}

    public function envelope(): Envelope
    {
        $dest = $this->request->destination?->name ?? 'your trip';

        return new Envelope(subject: "Your document checklist for {$dest}");
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Your full document checklist is ready and saved here: '
                .'<a href="'.url('/checklist/'.$this->request->token).'">view it any time</a>.</p>'
                .'<p>Independent service — not a government website. Service fee is separate from any government fee.</p>'
        );
    }
}
```

Then `app/Jobs/DeliverPaidChecklist.php`:

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\PaidChecklistMail;
use App\Models\ChecklistRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Post-pay delivery for the instant checklist.
 *
 * - Standard: reveal-only (no delivery here).
 * - Express/Premium: email the saved checklist (with PDF/.ics links) to the buyer if an
 *   email is on file.
 * - Premium: additionally flag a team-notify for the 1:1 WhatsApp review.
 */
final class DeliverPaidChecklist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $checklistRequestId) {}

    public function handle(): void
    {
        $request = ChecklistRequest::query()->with('destination')->find($this->checklistRequestId);
        if ($request === null || ! $request->isPaid()) {
            return;
        }

        $tier = (string) $request->tier;

        if (in_array($tier, ['express', 'premium'], true) && ! empty($request->email)) {
            Mail::to($request->email)->send(new PaidChecklistMail($request));
        }

        if ($tier === 'premium') {
            Log::info('Checklist Premium: queue 1:1 WhatsApp review.', [
                'token' => $request->token,
                'email' => $request->email,
                'destination' => $request->destination?->name,
            ]);
        }
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --filter=DeliverPaidChecklistTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/DeliverPaidChecklist.php app/Mail/PaidChecklistMail.php tests/Feature/DeliverPaidChecklistTest.php
git commit -m "feat(checklist): post-pay delivery job — Express+ email, Premium team notify

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 9: Retention sweep includes checklist_requests

**Files:**
- Create: `app/Console/Commands/PurgeExpiredChecklists.php`
- Modify: `routes/console.php` (or wherever the scheduler lives — verify with `grep -rn "PurgeExpiredDocuments" routes/ app/`)
- Test: `tests/Feature/PurgeExpiredChecklistsTest.php`

**Interfaces:**
- Produces: `php artisan ukv:purge-checklists` — deletes `checklist_requests` older than `config('ukv.doc_retention_days', 90)` days (by `created_at`). Holds personal inputs; GDPR #71.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChecklistRequest;
use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PurgeExpiredChecklistsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_old_requests_keeps_recent(): void
    {
        config(['ukv.doc_retention_days' => 90]);
        $d = Destination::factory()->create();

        $old = ChecklistRequest::create(['destination_id' => $d->id, 'inputs' => [], 'items' => []]);
        $old->forceFill(['created_at' => now()->subDays(120)])->save();

        $recent = ChecklistRequest::create(['destination_id' => $d->id, 'inputs' => [], 'items' => []]);

        $this->artisan('ukv:purge-checklists')->assertExitCode(0);

        $this->assertDatabaseMissing('checklist_requests', ['id' => $old->id]);
        $this->assertDatabaseHas('checklist_requests', ['id' => $recent->id]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PurgeExpiredChecklistsTest`
Expected: FAIL — command `ukv:purge-checklists` not found.

- [ ] **Step 3: Write the command**

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ChecklistRequest;
use Illuminate\Console\Command;

/**
 * GDPR retention (#71): delete checklist requests older than the retention window.
 * They hold the visitor's trip + situation inputs, so they expire like uploaded documents.
 */
final class PurgeExpiredChecklists extends Command
{
    protected $signature = 'ukv:purge-checklists';

    protected $description = 'GDPR retention: delete checklist requests older than the retention window';

    public function handle(): int
    {
        $days = (int) config('ukv.doc_retention_days', 90);
        $cutoff = now()->subDays($days);

        $deleted = ChecklistRequest::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Purged {$deleted} checklist request(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Schedule it (mirror the documents purge)**

Run `grep -rn "PurgeExpiredDocuments\|->daily()\|withSchedule" routes/console.php app/Console 2>/dev/null` to find the schedule. Add alongside the documents purge (in `routes/console.php` if that's the pattern):

```php
Schedule::command('ukv:purge-checklists')->daily();
```
(Match the existing import/style; if the documents purge isn't scheduled here, just register the command — the test does not require scheduling.)

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=PurgeExpiredChecklistsTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/PurgeExpiredChecklists.php routes/console.php tests/Feature/PurgeExpiredChecklistsTest.php
git commit -m "feat(checklist): GDPR retention purge for checklist_requests

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 10: Full suite + manual smoke

**Files:** none (verification).

- [ ] **Step 1: Run the whole suite**

Run: `php artisan test`
Expected: all green (new tests + no regressions). If `ChecklistServiceTest`/`CheckoutGuardTest`/`StripeWebhookTest` fail, reconcile — the webhook branch must not change order behaviour for non-checklist events.

- [ ] **Step 2: Manual end-to-end smoke (local)**

```bash
php artisan serve
```
- `/document-checklist` → fill step 1 + 2 → submit → lands on `/checklist/{token}` showing the **redacted peek** + tiers + consent + free WhatsApp.
- View Source: confirm only the teaser label is present (gate integrity).
- Tick consent, pick Express, submit → redirected to Stripe (or, with no Stripe keys configured, expect the `InvalidArgumentException`/redirect — note Stripe keys are a launch task #98).

- [ ] **Step 3: Commit any reconciliation fixes**

```bash
git add -A
git commit -m "test(checklist): full-suite reconciliation for instant paid checklist

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Self-Review

**Spec coverage:**
- §2 flow (gate after step 2, peek→pay→reveal) → Tasks 5, 6, 7. ✓
- §3 tiers redefined, "from"/exact pricing → Tasks 2, 5 (exact per-destination shown at the gate; floor helper omitted as YAGNI since destination is always known — noted in Task 2). ✓
- §4 data model → Task 1. ✓
- §5 Stripe + reveal + webhook sole writer + read-only return → Tasks 3, 5, 7. ✓
- §6 server-side redaction (no CSS blur) → Task 1 (`peek()`) + Task 5 (view + `assertDontSee` guard). ✓
- §7 compliance: consent waiver → Task 4 (`accepted`) + Task 5 (copy); no-refund copy → Task 5; price integrity → Task 4; idempotency/double-charge guard → Task 3 + Task 4; retention → Task 9; VAT flagged open (no task — correctly out of scope). ✓
- §8 tests → every task is TDD; redaction guard in Task 5, idempotency in Task 3. ✓

**Placeholder scan:** no TBD/TODO; every code step has full code. ✓

**Type consistency:** `markChecklistPaidByToken(string,?string,?string)`, `createChecklistSession(ChecklistRequest):string`, `isChecklistSessionPaid(string,string):bool`, `priceFor(Destination,string):float`, `cards(Destination):array`, `peek():array`, `isPaid():bool`, `DeliverPaidChecklist::dispatch(int)` — used consistently across Tasks 1–8. ✓

**Note for implementer:** the success-url Stripe placeholder `{CHECKOUT_SESSION_ID}` must be passed **literally** (Stripe substitutes it) — do not URL-encode it (Task 3).
