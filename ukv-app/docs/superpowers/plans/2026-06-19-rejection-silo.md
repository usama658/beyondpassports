# Rejection Silo Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the rejection-centred silo — a bespoke `/visa-refusals` hub and `/promise` page, guide-engine spokes (reapply, refusal-letter, appeal, reasons/{reason}, visa-type), per-country `/visa/{country}/refused`, a reusable promise-strip module, nav, and a compliance guard test suite.

**Architecture:** Refusal spokes are **evergreen `guides` rows** tagged with a new `cluster='refusal'` column + a `refusal_kind` (`reason|topic|type`), reusing the existing guide engine (resolution, freshness, compliance publish gate, schema) and the existing `public.guides.show` article template (extended with a `/visa-refusals` breadcrumb branch). The hub and `/promise` are bespoke Blade. Per-country `/visa/{country}/refused` already routes via `GuideController@showCountry` (the `Refused` GuideType + `refused` topic slug exist) — it needs content, not code.

**Tech Stack:** Laravel 12, Blade, Pest (`php artisan test`), MySQL. PHP CLI: `/c/xampp/php/php.exe`. Reuse: guide engine (`Guide`, `GuideService`, `GuideController`, `GuideResource`, `GuidesDraftCommand`), taxonomy enum `RejectionReason`, `public.guides.show`, `partials/site-header.blade.php`, `public/assets/ukv.css`.

---

## File Structure

**Create:**
- `app/Http/Controllers/RejectionController.php` — hub + spoke resolution for the `/visa-refusals` cluster.
- `app/Http/Controllers/PromiseController.php` — the `/promise` page (bespoke).
- `app/Enums/RefusalKind.php` — `reason | topic | type` (groups cluster guides for the hub + routing).
- `resources/views/public/visa-refusals/hub.blade.php` — bespoke hub.
- `resources/views/public/promise.blade.php` — bespoke offer page.
- `resources/views/partials/promise-strip.blade.php` — reusable badge module.
- `database/migrations/2026_06_19_000001_add_cluster_to_guides.php` — `cluster` + `refusal_kind` columns.
- `tests/Feature/RejectionSiloRoutesTest.php` — routing + collision + 301.
- `tests/Feature/RejectionComplianceGuardTest.php` — forbidden-phrase guard (the critical test).
- `tests/Unit/RejectionReasonClassificationTest.php` — promise-eligibility mapping.

**Modify:**
- `routes/web.php` — register the rejection routes (after the existing guide routes).
- `app/Models/Guide.php` — add `cluster`, `refusal_kind` to `$fillable` + casts + `scopeRefusal`.
- `app/Services/GuideService.php` — `refusalCluster()`, `resolveRefusalSpoke()`.
- `app/Enums/RejectionReason.php` — add `promiseEligibility()` method (classification).
- `resources/views/public/guides/show.blade.php` — `/visa-refusals` breadcrumb + canonical branch for cluster guides.
- `resources/views/partials/site-header.blade.php` — add "Refused?" + "Our Promise" nav links.
- `resources/views/public/destinations/show.blade.php` — include the promise-strip + link the refused page (money page).

---

## PHASE F1 — Foundations (code, unblocked)

### Task 1: `cluster` + `refusal_kind` columns on `guides`

**Files:**
- Create: `database/migrations/2026_06_19_000001_add_cluster_to_guides.php`
- Modify: `app/Models/Guide.php`
- Test: `tests/Feature/GuideClusterColumnTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GuideClusterColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_persists_cluster_and_refusal_kind(): void
    {
        $g = Guide::create([
            'slug' => 'insufficient-funds',
            'title' => 'Refused for insufficient funds',
            'excerpt' => 'What it means and how we stop it.',
            'status' => 'published',
            'published_at' => now(),
            'cluster' => 'refusal',
            'refusal_kind' => 'reason',
        ]);

        $this->assertSame('refusal', $g->fresh()->cluster);
        $this->assertSame('reason', $g->fresh()->refusal_kind);
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=GuideClusterColumnTest`
Expected: FAIL — `Unknown column 'cluster'`.

- [ ] **Step 3: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->string('cluster')->nullable()->after('guide_type')
                ->comment('null = standard guide; "refusal" = rejection-silo cluster');
            $table->string('refusal_kind')->nullable()->after('cluster')
                ->comment('reason|topic|type — groups refusal-cluster guides; null otherwise');
            $table->index(['cluster', 'refusal_kind']);
        });
    }

    public function down(): void
    {
        Schema::table('guides', function (Blueprint $table) {
            $table->dropIndex(['cluster', 'refusal_kind']);
            $table->dropColumn(['cluster', 'refusal_kind']);
        });
    }
};
```

- [ ] **Step 4: Add to the model**

In `app/Models/Guide.php`, add `'cluster',` and `'refusal_kind',` to `$fillable` (after `'guide_type',`). Add a scope after `scopeForDestination`:

```php
    /**
     * Scope to the rejection-silo cluster (evergreen refusal guides), optionally a single kind.
     */
    public function scopeRefusal(Builder $query, ?string $kind = null): Builder
    {
        $query->whereNull('destination_id')->where('cluster', 'refusal');

        return $kind === null ? $query : $query->where('refusal_kind', $kind);
    }
```

- [ ] **Step 5: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=GuideClusterColumnTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_06_19_000001_add_cluster_to_guides.php app/Models/Guide.php tests/Feature/GuideClusterColumnTest.php
git commit -m "feat(rejection): cluster + refusal_kind columns on guides"
```

### Task 2: `RefusalKind` enum + `GuideService` resolution

**Files:**
- Create: `app/Enums/RefusalKind.php`
- Modify: `app/Services/GuideService.php`
- Test: `tests/Feature/GuideServiceRefusalTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guide;
use App\Services\GuideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GuideServiceRefusalTest extends TestCase
{
    use RefreshDatabase;

    private function makeRefusal(string $slug, string $kind): Guide
    {
        return Guide::create([
            'slug' => $slug, 'title' => $slug, 'excerpt' => 'x',
            'status' => 'published', 'published_at' => now(),
            'cluster' => 'refusal', 'refusal_kind' => $kind, 'sort_order' => 0,
        ]);
    }

    public function test_resolve_refusal_spoke_matches_slug_and_kind(): void
    {
        $this->makeRefusal('reapply', 'topic');
        $svc = app(GuideService::class);

        $this->assertNotNull($svc->resolveRefusalSpoke('reapply', 'topic'));
        $this->assertNull($svc->resolveRefusalSpoke('reapply', 'reason'));   // wrong kind
        $this->assertNull($svc->resolveRefusalSpoke('missing', 'topic'));    // missing
    }

    public function test_cluster_lists_only_published_refusal_guides(): void
    {
        $this->makeRefusal('insufficient-funds', 'reason');
        $this->makeRefusal('reapply', 'topic');
        Guide::create(['slug' => 'turkey-evisa', 'title' => 't', 'excerpt' => 'x', 'status' => 'published', 'published_at' => now()]); // non-refusal

        $this->assertCount(2, app(GuideService::class)->refusalCluster());
        $this->assertCount(1, app(GuideService::class)->refusalCluster('reason'));
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=GuideServiceRefusalTest`
Expected: FAIL — `Call to undefined method ...resolveRefusalSpoke()`.

- [ ] **Step 3: Create the enum**

```php
<?php

namespace App\Enums;

/** How a refusal-cluster guide is grouped in the silo (drives URL segment + hub grouping). */
enum RefusalKind: string
{
    case Reason = 'reason';   // /visa-refusals/reasons/{slug}
    case Topic = 'topic';     // /visa-refusals/{slug}  (reapply, refusal-letter, appeal)
    case Type = 'type';       // /visa-refusals/{slug}  (visitor-visa, student-visa, spouse-visa)

    public function heading(): string
    {
        return match ($this) {
            self::Reason => 'Why visas get refused',
            self::Topic => 'After a refusal',
            self::Type => 'By visa type',
        };
    }
}
```

- [ ] **Step 4: Add the service methods**

In `app/Services/GuideService.php`, add after `evergreen()`:

```php
    /**
     * The published refusal-cluster guides (the hub's spokes), optionally one kind,
     * ordered by sort_order then title.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Guide>
     */
    public function refusalCluster(?string $kind = null): Collection
    {
        return Guide::query()
            ->published()
            ->refusal($kind)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    /**
     * Resolve a single published refusal spoke by slug + kind, or null (caller 404s).
     */
    public function resolveRefusalSpoke(string $slug, string $kind): ?Guide
    {
        return Guide::query()
            ->published()
            ->refusal($kind)
            ->where('slug', $slug)
            ->first();
    }
```

- [ ] **Step 5: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=GuideServiceRefusalTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Enums/RefusalKind.php app/Services/GuideService.php tests/Feature/GuideServiceRefusalTest.php
git commit -m "feat(rejection): RefusalKind enum + GuideService cluster resolution"
```

### Task 3: `RejectionReason::promiseEligibility()` classification

**Files:**
- Modify: `app/Enums/RejectionReason.php`
- Test: `tests/Unit/RejectionReasonClassificationTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\RejectionReason;
use PHPUnit\Framework\TestCase;

final class RejectionReasonClassificationTest extends TestCase
{
    public function test_classification_buckets(): void
    {
        // Things we prepared/checked → our_error (Promise covers a free re-prep).
        $this->assertSame('our_error', RejectionReason::DocQuality->promiseEligibility());
        $this->assertSame('our_error', RejectionReason::PassportValidity->promiseEligibility());
        $this->assertSame('our_error', RejectionReason::PortalError->promiseEligibility());

        // Applicant never qualified / withdrew → excluded.
        $this->assertSame('excluded', RejectionReason::Eligibility->promiseEligibility());
        $this->assertSame('excluded', RejectionReason::CustomerWithdrew->promiseEligibility());

        // Unknown/other → discretionary (revisitable bucket, case-by-case).
        $this->assertSame('discretionary_covered', RejectionReason::Other->promiseEligibility());
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionReasonClassificationTest`
Expected: FAIL — `Call to undefined method ...promiseEligibility()`.

- [ ] **Step 3: Add the method** to `app/Enums/RejectionReason.php` (before the closing brace)

```php
    /**
     * Promise-eligibility bucket — the single source for both the customer claim and the
     * ops decision. our_error = covered (free re-prep); discretionary_covered = covered,
     * revisitable; excluded = not covered. See rejection-proposition-design.md §2/§8.
     */
    public function promiseEligibility(): string
    {
        return match ($this) {
            self::DocQuality, self::PassportValidity, self::PortalError => 'our_error',
            self::Eligibility, self::CustomerWithdrew => 'excluded',
            self::Other => 'discretionary_covered',
        };
    }
```

- [ ] **Step 4: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionReasonClassificationTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Enums/RejectionReason.php tests/Unit/RejectionReasonClassificationTest.php
git commit -m "feat(rejection): promise-eligibility classification on RejectionReason"
```

### Task 4: Controllers — `PromiseController` + `RejectionController`

**Files:**
- Create: `app/Http/Controllers/PromiseController.php`, `app/Http/Controllers/RejectionController.php`
- (routes wired in Task 5; views in Tasks 6–7)

- [ ] **Step 1: Create `PromiseController`**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/** The Re-Application Promise explainer (bespoke). Static page; no model dependency. */
class PromiseController extends Controller
{
    public function show(): View
    {
        return view('public.promise');
    }
}
```

- [ ] **Step 2: Create `RejectionController`**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GuideService;
use Illuminate\Contracts\View\View;

/**
 * The rejection silo (root /visa-refusals).
 *
 *   GET /visa-refusals                  -> hub()            bespoke pillar, lists every spoke
 *   GET /visa-refusals/reasons/{reason} -> reason()         a reason spoke (guide engine)
 *   GET /visa-refusals/{slug}           -> spoke()          a topic/type spoke (guide engine)
 *
 * Spokes are evergreen `guides` (cluster=refusal) rendered by the shared guides.show view.
 * General guidance only; not a government website; the decision is the authority's.
 */
class RejectionController extends Controller
{
    public function __construct(private readonly GuideService $guides)
    {
    }

    public function hub(): View
    {
        return view('public.visa-refusals.hub', [
            'reasons' => $this->guides->refusalCluster('reason'),
            'topics' => $this->guides->refusalCluster('topic'),
            'types' => $this->guides->refusalCluster('type'),
        ]);
    }

    public function reason(string $reason): View
    {
        $guide = $this->guides->resolveRefusalSpoke($reason, 'reason');
        abort_if($guide === null, 404);

        return view('public.guides.show', ['guide' => $guide]);
    }

    public function spoke(string $slug): View
    {
        $guide = $this->guides->resolveRefusalSpoke($slug, 'topic')
            ?? $this->guides->resolveRefusalSpoke($slug, 'type');
        abort_if($guide === null, 404);

        return view('public.guides.show', ['guide' => $guide]);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/PromiseController.php app/Http/Controllers/RejectionController.php
git commit -m "feat(rejection): PromiseController + RejectionController"
```

### Task 5: Routes (with collision guard + rejection→refusal 301)

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/RejectionSiloRoutesTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RejectionSiloRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function publishRefusal(string $slug, string $kind): void
    {
        Guide::create([
            'slug' => $slug, 'title' => ucfirst($slug), 'excerpt' => 'General guidance.',
            'body' => '<p>General guidance.</p>', 'status' => 'published', 'published_at' => now(),
            'cluster' => 'refusal', 'refusal_kind' => $kind,
        ]);
    }

    public function test_promise_and_hub_render(): void
    {
        $this->get('/promise')->assertOk()->assertSee('Re-Application Promise');
        $this->get('/visa-refusals')->assertOk();
    }

    public function test_reason_and_spoke_resolve_without_collision(): void
    {
        $this->publishRefusal('insufficient-funds', 'reason');
        $this->publishRefusal('reapply', 'topic');
        $this->publishRefusal('visitor-visa', 'type');

        $this->get('/visa-refusals/reasons/insufficient-funds')->assertOk();
        $this->get('/visa-refusals/reapply')->assertOk();
        $this->get('/visa-refusals/visitor-visa')->assertOk();
        $this->get('/visa-refusals/reasons/nope')->assertNotFound();
        $this->get('/visa-refusals/nope')->assertNotFound();
    }

    public function test_rejection_synonym_redirects_to_refusal(): void
    {
        $this->get('/visa-rejections')->assertRedirect('/visa-refusals');
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionSiloRoutesTest`
Expected: FAIL — 404s on `/promise`, `/visa-refusals`.

- [ ] **Step 3: Add the imports + routes** to `routes/web.php`

Add imports near the top (after the existing `use ...Controllers` block):

```php
use App\Http\Controllers\PromiseController;
use App\Http\Controllers\RejectionController;
```

Add the routes after the existing guides routes (the `/guides/{slug}` block, ~line 37). Order matters — the static and `reasons/` routes are registered before the catch-all `{slug}`:

```php
// --- Rejection silo (the lead point of sale) ---
Route::get('/promise', [PromiseController::class, 'show'])->name('promise');
Route::redirect('/visa-rejections', '/visa-refusals', 301);
Route::get('/visa-refusals', [RejectionController::class, 'hub'])->name('refusals.hub');
Route::get('/visa-refusals/reasons/{reason}', [RejectionController::class, 'reason'])
    ->where('reason', '[a-z0-9-]+')
    ->name('refusals.reason');
Route::get('/visa-refusals/{slug}', [RejectionController::class, 'spoke'])
    ->where('slug', '(?!reasons$)[a-z0-9-]+')
    ->name('refusals.spoke');
```

- [ ] **Step 4: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionSiloRoutesTest`
Expected: PASS. (The hub + promise views are created in Tasks 6–7; if this task runs first, create minimal stub views `resources/views/public/visa-refusals/hub.blade.php` = `@extends('layouts.public') @section('content')<h1>Visa refusals</h1>@endsection` and `resources/views/public/promise.blade.php` containing "Re-Application Promise", then flesh them out in Tasks 6–7.)

- [ ] **Step 5: Commit**

```bash
git add routes/web.php tests/Feature/RejectionSiloRoutesTest.php resources/views/public/visa-refusals/hub.blade.php resources/views/public/promise.blade.php
git commit -m "feat(rejection): routes for /promise + /visa-refusals cluster (collision-guarded + 301)"
```

### Task 6: `/promise` page (bespoke Blade)

**Files:**
- Create/replace: `resources/views/public/promise.blade.php`

- [ ] **Step 1: Write the page** (extends the public layout; petrol/teal system; ✅/❌ table on the page; compliance strip; no approval %). Uses only design-system tokens from `public/assets/ukv.css`.

```blade
@extends('layouts.public')

@section('title', 'The Re-Application Promise | Beyond Passports')
@section('description', "If we prepare your UK visa application and it's refused for something we should have caught, we re-prepare and re-submit it free. Independent service — the decision is always the authority's.")
@section('canonical', url('/promise'))

@section('content')
<header class="wrap" style="padding:56px 0 8px;max-width:760px">
  <p class="eyebrow" style="color:var(--cta);font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:12px">Our promise</p>
  <h1 style="font-size:clamp(32px,4.6vw,52px);font-weight:800;letter-spacing:-.03em;color:var(--navy);line-height:1.1;margin:.2em 0 0">The Re-Application Promise</h1>
  <p style="font-size:20px;line-height:1.6;color:#33454f;margin:18px 0 0">The decision is always the authority's. What we control, we get right — and stand behind. If we prepare your application and it's refused for something we should have caught, we <strong>re-prepare and re-submit it free</strong>.</p>
</header>

<section class="wrap" style="max-width:760px;padding:36px 0">
  <h2 style="font-size:26px;color:var(--navy);font-weight:700;margin:0 0 16px">What the Promise covers</h2>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div style="border:1px solid var(--paper-edge);border-radius:14px;padding:20px;background:#f3faf7">
      <p style="font-weight:800;color:var(--stamp-text);margin:0 0 10px">✅ Covered — one free re-prep &amp; re-submission</p>
      <ul style="margin:0;padding-left:1.1em;line-height:1.6;color:var(--ink)">
        <li>Anything we prepared, checked or filed — incomplete or incorrect documents we handled.</li>
        <li>A requirement we should have flagged.</li>
        <li>A formatting, translation or apostille error we handled; a wrong category or typo we introduced.</li>
      </ul>
    </div>
    <div style="border:1px solid var(--paper-edge);border-radius:14px;padding:20px;background:#fbf6f4">
      <p style="font-weight:800;color:#9a4b3b;margin:0 0 10px">❌ Not covered</p>
      <ul style="margin:0;padding-left:1.1em;line-height:1.6;color:var(--ink)">
        <li>Fraud, or false or withheld information.</li>
        <li>Undisclosed ineligibility — you never qualified.</li>
        <li>A missed deadline, or a document you didn't supply.</li>
        <li>A government rule that changed after submission.</li>
      </ul>
    </div>
  </div>
  <p style="font-size:14px;color:var(--muted);margin:16px 0 0">You still pay any new government fee. Full terms: <a href="{{ url('/legal') }}#re-application-promise" style="color:var(--stamp-text)">the Re-Application Promise terms</a>.</p>
</section>

<section class="wrap" style="max-width:760px;padding:0 0 12px">
  <h2 style="font-size:26px;color:var(--navy);font-weight:700;margin:0 0 12px">And a Turnaround Promise</h2>
  <p style="font-size:17px;line-height:1.7;color:var(--ink);margin:0">We submit a complete, checked application within our stated working-day window of receiving your documents — or your service fee back. Two halves of the same idea: <strong>what we control, we get right</strong>.</p>
</section>

<section class="wrap" style="max-width:760px;padding:24px 0 8px">
  <p style="font-size:17px;line-height:1.7;color:var(--ink);margin:0">We don't publish approval rates, because no honest service can promise the authority's decision. We publish what we control — and stand behind it.</p>
</section>

@include('partials.compliance-strip')

<section class="cta-band"><div class="wrap">
  <div class="rule"></div>
  <h2>Start with your application checked</h2>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/visa-refusals') }}" class="btn btn--glass">Worried about refusal?</a>
  </div>
</div></section>
@endsection
```

- [ ] **Step 2: Reuse-or-create the compliance strip partial.** If `resources/views/partials/compliance-strip.blade.php` does not already exist, create it:

```blade
<section class="wrap" style="max-width:760px;padding:8px 0 28px">
  <p style="font-size:13px;color:var(--muted);border-top:1px solid var(--paper-edge);padding-top:14px;margin:0">
    <strong>Independent service · not a government website · the decision is the authority's.</strong>
    Beyond Passports is not affiliated with any government body. No service can guarantee a government decision.
  </p>
</section>
```

- [ ] **Step 3: Verify it renders**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionSiloRoutesTest::test_promise_and_hub_render`
Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add resources/views/public/promise.blade.php resources/views/partials/compliance-strip.blade.php
git commit -m "feat(rejection): /promise page + compliance-strip partial"
```

### Task 7: `/visa-refusals` hub (bespoke Blade)

**Files:**
- Create/replace: `resources/views/public/visa-refusals/hub.blade.php`

- [ ] **Step 1: Write the hub** — pillar that lists reason / topic / type spokes from the controller, links per-country refused pages, and the promise. Uses `$reasons`, `$topics`, `$types`.

```blade
@extends('layouts.public')

@section('title', 'UK visa refused — or worried it might be? | Beyond Passports')
@section('description', 'Why UK visas get refused, what each reason really means, how to reapply, and how we remove the avoidable causes before you submit. Independent service — the decision is the authority\'s.')
@section('canonical', url('/visa-refusals'))

@section('content')
<header class="wrap" style="padding:56px 0 8px;max-width:820px">
  <p class="eyebrow" style="color:var(--cta);font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:12px">Visa refusals</p>
  <h1 style="font-size:clamp(32px,4.6vw,52px);font-weight:800;letter-spacing:-.03em;color:var(--navy);line-height:1.1;margin:.2em 0 0">UK visa refused — or worried it might be?</h1>
  <p style="font-size:20px;line-height:1.6;color:#33454f;margin:18px 0 0">Most refusals are avoidable. Here's what each reason really means, what to do next, and how we remove the avoidable causes before you submit — backed by the <a href="{{ url('/promise') }}" style="color:var(--stamp-text)">Re-Application Promise</a>.</p>
</header>

@php
  $groups = [
    ['heading' => 'After a refusal', 'items' => $topics, 'base' => url('/visa-refusals')],
    ['heading' => 'Why visas get refused', 'items' => $reasons, 'base' => url('/visa-refusals/reasons')],
    ['heading' => 'By visa type', 'items' => $types, 'base' => url('/visa-refusals')],
  ];
@endphp

@foreach ($groups as $group)
  @if ($group['items']->isNotEmpty())
    <section class="wrap" style="max-width:820px;padding:28px 0 0">
      <h2 style="font-size:22px;color:var(--navy);font-weight:700;margin:0 0 8px">{{ $group['heading'] }}</h2>
      <ul style="list-style:none;margin:0;padding:0">
        @foreach ($group['items'] as $g)
          <li style="border-top:1px solid var(--paper-edge);padding:14px 0">
            <a href="{{ $group['base'].'/'.$g->slug }}" style="font-size:17px;font-weight:600;color:var(--navy);text-decoration:none">{{ $g->title }}</a>
            <span style="display:block;font-size:13.5px;color:var(--muted);margin-top:2px">{{ $g->excerpt }}</span>
          </li>
        @endforeach
      </ul>
    </section>
  @endif
@endforeach

<section class="wrap" style="max-width:820px;padding:28px 0 0">
  <h2 style="font-size:22px;color:var(--navy);font-weight:700;margin:0 0 8px">Refused for a specific destination?</h2>
  <p style="color:var(--muted);margin:0 0 8px">See the refusal guidance for your destination, alongside what it costs and how to apply.</p>
  <p><a href="{{ url('/destinations') }}" style="color:var(--stamp-text);font-weight:600">All destinations →</a></p>
</section>

@include('partials.promise-strip')
@include('partials.compliance-strip')

<section class="cta-band"><div class="wrap">
  <div class="rule"></div>
  <h2>Give your application its best chance</h2>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/document-checklist') }}" class="btn btn--glass">Check what I need</a>
  </div>
</div></section>
@endsection
```

- [ ] **Step 2: Verify it renders** (hub test from Task 5 covers `/visa-refusals` 200).

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionSiloRoutesTest`
Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add resources/views/public/visa-refusals/hub.blade.php
git commit -m "feat(rejection): /visa-refusals hub"
```

### Task 8: Promise-strip module + placements

**Files:**
- Create: `resources/views/partials/promise-strip.blade.php`
- Modify: `resources/views/public/destinations/show.blade.php` (money page), `resources/views/public.home` (home), `resources/views/public/apply.blade.php` (pre-payment)
- Test: `tests/Feature/PromiseStripTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PromiseStripTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_promise_strip_linking_to_promise(): void
    {
        $this->get('/')->assertOk()->assertSee(url('/promise'));
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=PromiseStripTest`
Expected: FAIL — `/promise` link not present on home.

- [ ] **Step 3: Create the partial**

```blade
{{-- Re-Application Promise strip — passport-stamp signature + one-liner + link. --}}
<section class="wrap" style="max-width:820px;padding:20px 0">
  <div style="display:flex;align-items:center;gap:16px;border:1px solid var(--paper-edge);border-radius:14px;padding:18px 22px;background:#f3faf7">
    <span class="stamp" aria-hidden="true" style="flex:0 0 auto">✓</span>
    <p style="margin:0;font-size:15px;line-height:1.5;color:var(--ink)">
      <strong>The Re-Application Promise.</strong> Refused for something we should have caught? We re-prepare and re-submit free.
      <a href="{{ url('/promise') }}" style="color:var(--stamp-text);font-weight:600;white-space:nowrap">How it works →</a>
    </p>
  </div>
</section>
```

- [ ] **Step 4: Include it** on home (`resources/views/public/home.blade.php` — add `@include('partials.promise-strip')` before the final CTA band), on the money page (`destinations/show.blade.php` — after the pricing section), and on apply (`apply.blade.php` — before the payment step). Add exactly this line at each site:

```blade
@include('partials.promise-strip')
```

- [ ] **Step 5: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=PromiseStripTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/partials/promise-strip.blade.php resources/views/public/home.blade.php resources/views/public/destinations/show.blade.php resources/views/public/apply.blade.php tests/Feature/PromiseStripTest.php
git commit -m "feat(rejection): promise-strip module + home/money/apply placements"
```

### Task 9: Nav links + cluster breadcrumb branch

**Files:**
- Modify: `resources/views/partials/site-header.blade.php`, `resources/views/public/guides/show.blade.php`
- Test: `tests/Feature/RejectionNavTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RejectionNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_links_to_hub_and_promise(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString(url('/visa-refusals'), $html);
        $this->assertStringContainsString(url('/promise'), $html);
    }

    public function test_refusal_spoke_breadcrumb_points_to_hub(): void
    {
        Guide::create([
            'slug' => 'reapply', 'title' => 'Reapplying after a refusal', 'excerpt' => 'x',
            'body' => '<p>x</p>', 'status' => 'published', 'published_at' => now(),
            'cluster' => 'refusal', 'refusal_kind' => 'topic',
        ]);

        $this->get('/visa-refusals/reapply')->assertOk()
            ->assertSee(url('/visa-refusals'))      // breadcrumb up to hub
            ->assertDontSee(url('/guides'));         // not the generic guides crumb
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionNavTest`
Expected: FAIL — nav links absent; spoke breadcrumb still says `/guides`.

- [ ] **Step 3: Add nav links** in `resources/views/partials/site-header.blade.php`, before the `About` link (line ~46):

```blade
    <a href="{{ url('/visa-refusals') }}">Refused?</a>
    <a href="{{ url('/promise') }}">Our Promise</a>
```

- [ ] **Step 4: Add the cluster breadcrumb + canonical branch** in `resources/views/public/guides/show.blade.php`. Replace the URL + crumbs computation so a refusal-cluster guide points at `/visa-refusals`. In the `@php` block, after `$guideUrl = $isCountry ? ... : url('/guides/'.$guide->slug);`, insert:

```php
    // Rejection-cluster evergreen guides live under /visa-refusals, not /guides.
    $isRefusal = (! $isCountry) && $guide->cluster === 'refusal';
    if ($isRefusal) {
        $guideUrl = $guide->refusal_kind === 'reason'
            ? url('/visa-refusals/reasons/'.$guide->slug)
            : url('/visa-refusals/'.$guide->slug);
    }
```

Then in the breadcrumb `else` branch (currently `Guides` → `/guides`), make it cluster-aware:

```php
    } elseif ($isRefusal) {
        $crumbs[] = ['name' => 'Visa refusals', 'url' => url('/visa-refusals')];
        $crumbs[] = ['name' => $guide->title, 'url' => $guideUrl];
    } else {
        $crumbs[] = ['name' => 'Guides', 'url' => url('/guides')];
        $crumbs[] = ['name' => $guide->title, 'url' => $guideUrl];
    }
```

- [ ] **Step 5: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionNavTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/partials/site-header.blade.php resources/views/public/guides/show.blade.php tests/Feature/RejectionNavTest.php
git commit -m "feat(rejection): nav links + /visa-refusals breadcrumb branch for cluster guides"
```

---

## PHASE F2 — Fill taxonomy + reason set (data, unblocked)

### Task 10: Author the reason content source

**Files:**
- Create: `database/seeders/RefusalReasonSeeder.php`

This is content data, not new engine code. Each reason becomes a published refusal-cluster guide via the seeder, following the locked skeleton (decode → recoverable/blocking → how we stop it → reapply guidance → tool entry → promise close → freshness → compliance). Body is honest, no approval %.

- [ ] **Step 1: Create the seeder** with the demand-led reason set (slugs from §2 keyword work): `insufficient-funds`, `false-or-misleading-information`, `weak-ties-to-home`, `credibility-interview`, `sponsorship-or-invitation`, `previous-immigration-history`, `incomplete-documents`, `travel-insurance` (Schengen-style, where relevant). For each, set `cluster='refusal'`, `refusal_kind='reason'`, `reviewed_by` left null until the named lead is supplied (Task input #320), `reviewed_at`=null, `status='published'`, `published_at=now()`. Body uses the skeleton; classification language matches `RejectionReason::promiseEligibility()` buckets.

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;

final class RefusalReasonSeeder extends Seeder
{
    /** @var list<array{slug:string,title:string,excerpt:string,quick:string,body:string}> */
    private const REASONS = [
        [
            'slug' => 'insufficient-funds',
            'title' => 'Refused for insufficient funds — what it means',
            'excerpt' => 'Usually about the evidence, not the money. Here is what the officer looked for and how we make sure your file shows it.',
            'quick' => 'A funds refusal usually means your evidence did not clearly show you can cover the trip — often a fixable evidence problem, not ineligibility.',
            'body' => '<h2>What the officer saw</h2><p>Your evidence did not clearly show you can cover the trip without working unlawfully or relying on public funds. In plain terms, the bank balance, its history, or the source of the money was not clear enough — not necessarily that you do not have the money.</p><h2>Recoverable or blocking?</h2><p>Usually <strong>recoverable</strong> — this is about evidence. It is only blocking if you genuinely do not meet the maintenance requirement.</p><h2>How we stop it</h2><ul><li>Our eligibility screen flags the maintenance threshold before you pay.</li><li>Our document check confirms six months of history, a named account, and a clear source of funds.</li><li>A real UK person checks the file before anything is submitted.</li></ul><h2>If you are reapplying</h2><p>Disclose your previous refusal on the new application, and only reapply once something has changed — stronger evidence, not the same file resubmitted.</p>',
        ],
        // ... remaining reasons authored to the same skeleton ...
    ];

    public function run(): void
    {
        foreach (self::REASONS as $i => $r) {
            Guide::updateOrCreate(
                ['slug' => $r['slug'], 'cluster' => 'refusal'],
                [
                    'destination_id' => null,
                    'guide_type' => null,
                    'refusal_kind' => 'reason',
                    'title' => $r['title'],
                    'excerpt' => $r['excerpt'],
                    'quick_answer' => $r['quick'],
                    'body' => $r['body'],
                    'status' => 'published',
                    'published_at' => now(),
                    'sort_order' => $i,
                ],
            );
        }
    }
}
```

> NOTE: Author every reason in `REASONS` in full (no `// ...` left in the committed file). Each body must follow the skeleton and contain zero approval-rate / "guarantee" language — the compliance guard test (Task 14) will fail otherwise.

- [ ] **Step 2: Seed + verify**

Run: `/c/xampp/php/php.exe artisan db:seed --class=Database\\Seeders\\RefusalReasonSeeder`
Then: `/c/xampp/php/php.exe artisan test --filter=RejectionSiloRoutesTest`
Expected: PASS; visiting `/visa-refusals/reasons/insufficient-funds` returns 200.

- [ ] **Step 3: Commit**

```bash
git add database/seeders/RefusalReasonSeeder.php
git commit -m "feat(rejection): reason-spoke content seeder (taxonomy #73, honest skeleton)"
```

---

## PHASE F3 — Reason spokes live (covered by Task 10 seeder + the engine)

Reason spokes render through `RejectionController@reason` + `guides.show` once seeded. No extra code. Optionally generate richer drafts via the existing `GuidesDraftCommand` then publish through the Filament `GuideResource` compliance gate — but the seeder ships a compliant baseline. Verify all reason slugs return 200 (Task 14 suite covers this).

---

## PHASE F4 — Type + evergreen spokes (data, unblocked)

### Task 11: Type + evergreen spoke seeder

**Files:**
- Create: `database/seeders/RefusalSpokeSeeder.php`

- [ ] **Step 1: Create the seeder** for the topic + type spokes, same pattern as Task 10:
  - **topic** (`refusal_kind='topic'`): `reapply` ("Your chances after a UK visa refusal" — the highest-demand term; honest, no rate), `refusal-letter` ("Understand your UK visa refusal letter" — decode walkthrough), `appeal` ("Appeal vs reapply" — informational; state we provide guidance, not regulated appeal representation).
  - **type** (`refusal_kind='type'`): `visitor-visa`, `student-visa`, `spouse-visa` — type-specific refusal causes, each linking to the relevant `/reasons/{reason}` pages.

Each row: `cluster='refusal'`, the correct `refusal_kind`, `status='published'`, `published_at=now()`, body to the skeleton, no approval %.

- [ ] **Step 2: Seed + verify**

Run: `/c/xampp/php/php.exe artisan db:seed --class=Database\\Seeders\\RefusalSpokeSeeder`
Then check `/visa-refusals/reapply`, `/visa-refusals/visitor-visa` return 200.

- [ ] **Step 3: Commit**

```bash
git add database/seeders/RefusalSpokeSeeder.php
git commit -m "feat(rejection): topic + type spoke content (reapply/refusal-letter/appeal + visa types)"
```

---

## PHASE F5 — Per-country /visa/{country}/refused (BLOCKED on gov.uk-verified data #129/#299)

### Task 12: Per-country refused guides

**Files:**
- Create: `database/seeders/CountryRefusedSeeder.php`

- [ ] **Step 1:** For each of the 8 destinations, create a country guide with `guide_type='refused'` (the `Refused` GuideType already routes via `/visa/{slug}/refused`), `destination_id` set, body to the skeleton using **gov.uk-verified** per-country refusal data. Until that data is verified (#129/#299), seed only destinations whose refusal data is confirmed; leave the rest unpublished. Each links its money page + the relevant `/reasons/{reason}`.

- [ ] **Step 2: Seed + verify** a confirmed destination returns 200 at `/visa/{slug}/refused`.

- [ ] **Step 3: Commit**

```bash
git add database/seeders/CountryRefusedSeeder.php
git commit -m "feat(rejection): per-country /refused content (verified destinations only)"
```

---

## PHASE F6 — Polish + tests (code, unblocked)

### Task 13: Sitemap includes the rejection silo

**Files:**
- Modify: `app/Http/Controllers/SitemapController.php`
- Test: `tests/Feature/SitemapRejectionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SitemapRejectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_hub_promise_and_spokes(): void
    {
        Guide::create([
            'slug' => 'insufficient-funds', 'title' => 'x', 'excerpt' => 'x', 'body' => '<p>x</p>',
            'status' => 'published', 'published_at' => now(), 'cluster' => 'refusal', 'refusal_kind' => 'reason',
        ]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString(url('/visa-refusals'), $xml);
        $this->assertStringContainsString(url('/promise'), $xml);
        $this->assertStringContainsString(url('/visa-refusals/reasons/insufficient-funds'), $xml);
    }
}
```

- [ ] **Step 2: Run it — expect failure**

Run: `/c/xampp/php/php.exe artisan test --filter=SitemapRejectionTest`
Expected: FAIL — URLs absent from the sitemap.

- [ ] **Step 3: Add the URLs.** In `SitemapController`, add `/visa-refusals` and `/promise` as static entries, and append the refusal cluster from the engine. Use the existing URL-collection pattern in that controller; add:

```php
// Rejection silo
$urls[] = url('/visa-refusals');
$urls[] = url('/promise');
foreach (\App\Models\Guide::query()->published()->refusal()->get() as $g) {
    $urls[] = $g->refusal_kind === 'reason'
        ? url('/visa-refusals/reasons/'.$g->slug)
        : url('/visa-refusals/'.$g->slug);
}
```

- [ ] **Step 4: Run it — expect pass**

Run: `/c/xampp/php/php.exe artisan test --filter=SitemapRejectionTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/SitemapController.php tests/Feature/SitemapRejectionTest.php
git commit -m "feat(rejection): rejection silo in sitemap"
```

### Task 14: Compliance guard test (the critical gate)

**Files:**
- Test: `tests/Feature/RejectionComplianceGuardTest.php`

- [ ] **Step 1: Write the test** — every rejection surface renders 200, carries the "not a government website" line, and contains NONE of the forbidden phrases.

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\RefusalReasonSeeder;
use Database\Seeders\RefusalSpokeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RejectionComplianceGuardTest extends TestCase
{
    use RefreshDatabase;

    /** @return list<string> */
    private function forbidden(): array
    {
        return ['approval rate', '% approved', 'approved %', 'guarantee approval',
            'guaranteed approval', 'rejection-proof', '99%', '95%'];
    }

    private function assertCompliant(string $path): void
    {
        $html = strtolower($this->get($path)->assertOk()->getContent());
        $this->assertStringContainsString('not a government website', $html, "missing disclaimer on {$path}");
        foreach ($this->forbidden() as $bad) {
            $this->assertStringNotContainsString(strtolower($bad), $html, "forbidden phrase '{$bad}' on {$path}");
        }
    }

    public function test_all_rejection_surfaces_are_compliant(): void
    {
        (new RefusalReasonSeeder())->run();
        (new RefusalSpokeSeeder())->run();

        $this->assertCompliant('/promise');
        $this->assertCompliant('/visa-refusals');
        $this->assertCompliant('/visa-refusals/reasons/insufficient-funds');
        $this->assertCompliant('/visa-refusals/reapply');
        $this->assertCompliant('/visa-refusals/visitor-visa');
    }
}
```

- [ ] **Step 2: Run it.** Expected: PASS. If it fails on a forbidden phrase, fix the *content* (seeder body), not the test — the test is the contract.

Run: `/c/xampp/php/php.exe artisan test --filter=RejectionComplianceGuardTest`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/RejectionComplianceGuardTest.php
git commit -m "test(rejection): compliance guard — no approval-% / guarantee language on any rejection surface"
```

### Task 15: Full regression + freshness sweep

- [ ] **Step 1: Run the whole suite**

Run: `/c/xampp/php/php.exe artisan test`
Expected: all green (existing 146 + the new rejection tests).

- [ ] **Step 2: Once the named UK case-lead is supplied (#320)**, set `reviewed_by` + `reviewed_at` on the seeded guides so the freshness byline ("Reviewed {date} by …") and `Article.dateModified` render. Until then they correctly show "Published" with no fabricated reviewer.

- [ ] **Step 3: Commit any content updates**

```bash
git add database/seeders/
git commit -m "chore(rejection): freshness byline once reviewer supplied"
```

---

## Self-Review

**Spec coverage:** §3 URL map → Tasks 5–7,10–12. §4 page-types/engine → Tasks 1–4,9. §5 nav/module/links/SEO → Tasks 8,9,13. §6 content/population → Tasks 10–12. §7 compliance → Tasks 6,14. §8 testing → Tasks 1–3,5,8,9,13,14,15. §9 phases F1–F6 → Phase headers. §10 inputs → noted in Tasks 10,15 (reviewer null until supplied). All covered.

**Placeholder scan:** Task 10/11/12 seeder bodies are intentionally content-authoring tasks with an explicit "author in full, no `// ...`" instruction and the compliance guard (Task 14) as the enforcement — this is data entry, not a code placeholder. No "TBD"/"add error handling"/undefined-symbol references in code steps.

**Type consistency:** `cluster`/`refusal_kind` (string columns) used identically across Tasks 1,2,9,13. `RefusalKind` values (`reason|topic|type`) match `refusal_kind` strings and the route handlers. `promiseEligibility()` buckets match the unit test. `resolveRefusalSpoke($slug,$kind)` / `refusalCluster(?$kind)` signatures consistent across Tasks 2,4,13.
