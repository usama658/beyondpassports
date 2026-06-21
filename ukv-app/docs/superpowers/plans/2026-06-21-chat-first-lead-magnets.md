# Chat-first Lead Magnets — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make WhatsApp chat the universal capture channel — a shared `wa-cta` partial, a site-wide floating "chat to a real UK person" button, and a per-destination chat CTA.

**Architecture:** One presentational Blade partial (`partials.wa-cta`) is the single source for every WhatsApp link (builds the href from `config('ukv.whatsapp')` + an optional prefilled message). A floating-button partial (`partials.wa-float`) wraps it and is included in the layout + the two standalone pages. The destination money page swaps its generic chat link for a destination-specific `wa-cta`.

**Tech Stack:** Laravel 12, Blade (no build step, raw `asset()`), PHPUnit (`Tests\TestCase`, `RefreshDatabase`), MySQL. Local PHP: `/c/xampp/php/php.exe`. Run tests: `php artisan test`.

## Global Constraints

- **Spec:** `ukv-app/docs/superpowers/specs/2026-06-21-chat-first-lead-magnets-design.md`.
- **Number source:** every WhatsApp link reads `config('ukv.whatsapp') ?: '440000000000'` (placeholder until #339). Never hardcode a number.
- **No PHP helper / no composer change** — the shared unit is a Blade partial (deploy-safe; deploy runs no `composer dump-autoload` for views).
- **Message encoding:** `urlencode()` the message into `?text=` (spaces become `+`).
- **WhatsApp glyph (inline, svg-symbols has none):** path `d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"`.
- **Brand green:** `#25D366` (WhatsApp), hover `#1da851`. Tokens: `--display`, `--navy`.
- **Float z-index:** `35` (below the checklist sticky bar's `40`/`60`).
- **Commit trailer:** end messages with `Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>`.
- **Out of scope:** Tools-hub reframe (#350), migrating the ~16 existing inline `wa.me` links.

## File Structure

- `resources/views/partials/wa-cta.blade.php` — the link component (href + glyph + label + variant styles, emitted `@once`).
- `resources/views/partials/wa-float.blade.php` — fixed-position wrapper around `wa-cta` (own `@once` style).
- `resources/views/layouts/public.blade.php` — include float once.
- `resources/views/track.blade.php` + `resources/views/public/checklist-result.blade.php` — standalone pages; include float.
- `resources/views/destinations/show.blade.php` — swap generic chat link for destination-specific `wa-cta`.
- `tests/Feature/ChatCtaTest.php` — all tests.

---

### Task 1: `wa-cta` shared partial

**Files:**
- Create: `resources/views/partials/wa-cta.blade.php`
- Test: `tests/Feature/ChatCtaTest.php`

**Interfaces:**
- Produces: a Blade partial included as
  `@include('partials.wa-cta', ['message' => ?string, 'label' => string, 'variant' => 'primary'|'ghost'|'floating'])`.
  Renders `<a data-wa-cta href="https://wa.me/{number}?text={urlencoded message}" target="_blank" rel="noopener" class="wa-cta wa-cta--{variant}">`. `message` optional (omit → no `?text=`); `label` defaults to "Chat on WhatsApp"; `variant` defaults to `primary`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ChatCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_wa_cta_builds_link_with_encoded_message_and_label(): void
    {
        config(['ukv.whatsapp' => '447700900123']);

        $html = view('partials.wa-cta', [
            'message' => 'Help with Turkey',
            'label' => 'Ask on WhatsApp',
        ])->render();

        $this->assertStringContainsString('https://wa.me/447700900123', $html);
        $this->assertStringContainsString('text=Help+with+Turkey', $html); // urlencode → spaces as '+'
        $this->assertStringContainsString('Ask on WhatsApp', $html);
        $this->assertStringContainsString('rel="noopener"', $html);
        $this->assertStringContainsString('data-wa-cta', $html);
    }

    public function test_wa_cta_without_message_has_no_text_param(): void
    {
        config(['ukv.whatsapp' => '447700900123']);

        $html = view('partials.wa-cta', ['label' => 'Chat'])->render();

        $this->assertStringContainsString('https://wa.me/447700900123', $html);
        $this->assertStringNotContainsString('?text=', $html);
    }

    public function test_wa_cta_falls_back_to_placeholder_number_when_unset(): void
    {
        config(['ukv.whatsapp' => '']);

        $html = view('partials.wa-cta', ['label' => 'Chat'])->render();

        $this->assertStringContainsString('https://wa.me/440000000000', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: FAIL — `View [partials.wa-cta] not found`.

- [ ] **Step 3: Create the partial**

`resources/views/partials/wa-cta.blade.php`:

```blade
@php
    /** Shared WhatsApp CTA. Single source for every wa.me link.
     *  Params: message (?string), label (string), variant (primary|ghost|floating). */
    $waNumber  = config('ukv.whatsapp') ?: '440000000000';
    $waMessage = $message ?? null;
    $waHref    = 'https://wa.me/'.$waNumber.(! empty($waMessage) ? '?text='.urlencode($waMessage) : '');
    $waLabel   = $label ?? 'Chat on WhatsApp';
    $waVariant = $variant ?? 'primary';
@endphp
<a href="{{ $waHref }}" target="_blank" rel="noopener" data-wa-cta class="wa-cta wa-cta--{{ $waVariant }}">
    <svg viewBox="0 0 24 24" aria-hidden="true" class="wa-cta__glyph"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
    <span class="wa-cta__label">{{ $waLabel }}</span>
</a>
@once
<style>
  .wa-cta{display:inline-flex;align-items:center;gap:9px;text-decoration:none;white-space:nowrap;
    font:800 15px var(--display);border-radius:12px;padding:13px 22px;transition:background .15s,filter .15s,transform .08s}
  .wa-cta__glyph{width:20px;height:20px;fill:currentColor;flex:none}
  .wa-cta--primary{background:#25D366;color:#fff;box-shadow:0 12px 26px -12px rgba(37,211,102,.7)}
  .wa-cta--primary:hover{background:#1da851}
  .wa-cta--ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.25);color:#fff}
  .wa-cta--ghost:hover{background:rgba(255,255,255,.16)}
  .wa-cta--floating{background:#25D366;color:#fff;box-shadow:0 16px 34px -12px rgba(37,211,102,.8)}
  .wa-cta--floating:hover{background:#1da851;transform:translateY(-1px)}
</style>
@endonce
```

- [ ] **Step 4: Run test to verify it passes**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/wa-cta.blade.php tests/Feature/ChatCtaTest.php
git commit -m "feat(chat): shared wa-cta partial (single source for WhatsApp links)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Site-wide floating WhatsApp button

**Files:**
- Create: `resources/views/partials/wa-float.blade.php`
- Modify: `resources/views/layouts/public.blade.php` (after `</main>`, before `@include('partials.site-footer')`)
- Modify: `resources/views/track.blade.php` (before `@include('partials.site-footer')`, ~line 293)
- Modify: `resources/views/public/checklist-result.blade.php` (before `@include('partials.site-footer')`, ~line 570)
- Test: `tests/Feature/ChatCtaTest.php` (add methods)

**Interfaces:**
- Consumes: `partials.wa-cta` (Task 1) with `variant=floating`.
- Produces: `@include('partials.wa-float')` → fixed bottom-right element marked `data-wa-float`.

- [ ] **Step 1: Write the failing test (append to ChatCtaTest)**

```php
    public function test_floating_button_present_on_layout_page(): void
    {
        $this->get('/')->assertOk()->assertSee('data-wa-float', false);
    }

    public function test_floating_button_present_on_standalone_track_page(): void
    {
        $this->get('/track')->assertOk()->assertSee('data-wa-float', false);
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: FAIL — `data-wa-float` not found on `/` or `/track`.

- [ ] **Step 3: Create the float partial**

`resources/views/partials/wa-float.blade.php`:

```blade
{{-- Site-wide floating WhatsApp button. Chat = the universal capture channel. --}}
<div class="wa-float" data-wa-float>
    @include('partials.wa-cta', [
        'message' => "Hi Beyond Passports — I'd like some help with my trip.",
        'label' => 'Chat to a real UK person',
        'variant' => 'floating',
    ])
</div>
@once
<style>
  .wa-float{position:fixed;right:18px;bottom:18px;z-index:35}
  /* Mobile: collapse to a circular FAB (glyph only). */
  @media (max-width:620px){
    .wa-float{right:14px;bottom:14px}
    .wa-float .wa-cta{padding:14px;border-radius:50%}
    .wa-float .wa-cta__label{display:none}
  }
</style>
@endonce
```

- [ ] **Step 4: Include it in the layout + two standalone pages**

In `resources/views/layouts/public.blade.php`, change the region after `</main>`:
```blade
</main>

@include('partials.wa-float')

@include('partials.site-footer')
```

In `resources/views/track.blade.php`, immediately before `@include('partials.site-footer')`:
```blade
@include('partials.wa-float')

@include('partials.site-footer')
```

In `resources/views/public/checklist-result.blade.php`, immediately before `@include('partials.site-footer')`:
```blade
@include('partials.wa-float')

@include('partials.site-footer')
```

- [ ] **Step 5: Run to verify it passes**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add resources/views/partials/wa-float.blade.php resources/views/layouts/public.blade.php resources/views/track.blade.php resources/views/public/checklist-result.blade.php tests/Feature/ChatCtaTest.php
git commit -m "feat(chat): site-wide floating WhatsApp button

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Per-destination chat CTA

**Files:**
- Modify: `resources/views/destinations/show.blade.php` (the `cta-band` row, ~line 363 — the generic "Chat on WhatsApp" link)
- Test: `tests/Feature/ChatCtaTest.php` (add method)

**Interfaces:**
- Consumes: `partials.wa-cta` (Task 1). `$destination` (Eloquent model) and `$name = $destination->name` are already in scope in this view.

- [ ] **Step 1: Write the failing test (append to ChatCtaTest)**

```php
    public function test_destination_money_page_has_destination_specific_chat_cta(): void
    {
        $d = \App\Models\Destination::create([
            'name' => 'Turkey',
            'slug' => 'turkey',
            'visa_type' => 'evisa',
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 39.00,
            'tier_express_gbp' => 59.00,
            'tier_premium_gbp' => 89.00,
            'passport_validity_months' => 6,
        ]);

        $html = $this->get('/visa/turkey')->assertOk()->getContent();

        // wa.me link whose prefilled text names the destination (urlencoded → '+').
        $this->assertStringContainsString('wa.me/', $html);
        $this->assertStringContainsString('documents+for+Turkey', $html);
    }
```

- [ ] **Step 2: Run to verify it fails**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: FAIL — page has the generic chat link, not the `documents+for+Turkey` message.

- [ ] **Step 3: Swap the generic link for a destination-specific `wa-cta`**

In `resources/views/destinations/show.blade.php`, the CTA-band row currently reads:
```blade
  <div class="row"><a href="#pricing" class="btn">Start my {{ $name }} application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a></div>
```
Replace it with:
```blade
  <div class="row">
    <a href="#pricing" class="btn">Start my {{ $name }} application →</a>
    @include('partials.wa-cta', [
        'message' => "Hi Beyond Passports — I'd like help with my documents for {$name}.",
        'label' => 'Ask about '.$name.' on WhatsApp',
        'variant' => 'ghost',
    ])
  </div>
```
(`variant=ghost` because the cta-band is a dark/navy surface — the ghost style is white-on-dark.)

- [ ] **Step 4: Run to verify it passes**

Run: `/c/xampp/php/php.exe artisan test --filter=ChatCtaTest`
Expected: PASS (6 tests).

- [ ] **Step 5: Commit**

```bash
git add resources/views/destinations/show.blade.php tests/Feature/ChatCtaTest.php
git commit -m "feat(chat): destination-specific WhatsApp CTA on money pages

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Full suite + smoke

**Files:** none (verification).

- [ ] **Step 1: Run the whole suite**

Run: `/c/xampp/php/php.exe artisan test`
Expected: all green (new ChatCtaTest + no regressions). If the home or track page test fails to find `data-wa-float`, confirm the include lines landed in the right files.

- [ ] **Step 2: Manual smoke**

```bash
php artisan serve
```
- `/` → floating green button bottom-right; click → opens `wa.me/...`.
- `/visa/turkey` (or any seeded destination) → CTA band shows "Ask about Turkey on WhatsApp"; link text names the destination.
- `/document-checklist` → floating button present, sits above (not under) nothing critical; on mobile it's a circular FAB and doesn't cover the sticky action bar.
- `/track` → floating button present.

- [ ] **Step 3: Commit any fixes**

```bash
git add -A
git commit -m "test(chat): full-suite verification for chat-first lead magnets

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Self-Review

**Spec coverage:**
- §3 wa-cta partial → Task 1 ✓
- §4 floating button (layout + track + checklist-result, z-index 35) → Task 2 ✓
- §5 per-destination CTA (message names destination) → Task 3 ✓
- §6 testing (render, presence, per-destination, no-number safety) → Tasks 1–3 tests ✓ (no-number = `test_wa_cta_falls_back_to_placeholder_number_when_unset`)
- §2 out-of-scope (Tools hub, 16-link migration) → not in any task ✓ (correctly excluded)

**Placeholder scan:** none — every step has full code/commands.

**Type consistency:** `wa-cta` include signature (`message`/`label`/`variant`) identical across Tasks 1–3; `data-wa-cta`/`data-wa-float` markers consistent between partials and tests; glyph path identical to Global Constraints.

**Note for implementer:** `@once` blocks ensure the `wa-cta`/`wa-float` `<style>` emits a single time even when the partial renders many times on one page (e.g. destination CTA + floating button). Don't move the styles out of `@once`.
