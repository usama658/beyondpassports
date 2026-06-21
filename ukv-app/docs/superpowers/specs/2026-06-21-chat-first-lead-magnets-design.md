# Chat-first lead magnets — design

**Date:** 2026-06-21
**Status:** approved (design), pending implementation plan
**Topic:** Make WhatsApp chat the universal capture channel — every lead magnet delivers value, then drops the visitor into chat with a real UK person. This round ships a shared chat component, a site-wide floating button, and per-destination chat CTAs.

---

## 1. Goal & positioning

Leads land in **WhatsApp chat**, not an email list. The site's job is: deliver value (free tools, guides, destination info) → invite the visitor to **"chat to a real UK person."** Reinforces the brand line "a real UK person checks every application." Email/HubSpot capture stays as the existing backup (checklist, contact/callback).

SEO content (guides, destinations) stays **ungated**; capture happens at the value→chat step.

The number is `config('ukv.whatsapp')` (env `UKV_WHATSAPP`). It is currently the placeholder `440000000000` (#339). Per decision, **wire it now** — every CTA reads the config, so it flips to the real number automatically once #339 lands. No graceful-hide this round.

---

## 2. Scope

**In scope (this round):**
1. Shared `partials.wa-cta` Blade partial (single source for WhatsApp links).
2. Site-wide floating WhatsApp button (`partials.wa-float`).
3. Per-destination contextual chat CTA on the 37 money pages.

**Out of scope (deferred):**
- Tools hub → "Free help" reframing (task #350, later round).
- Refactoring the existing ~16 inline `wa.me` links (migrate opportunistically, not now).
- Real WhatsApp number (#339, user-owned).

---

## 3. Component: `partials.wa-cta`

A presentational Blade partial — the only new shared unit. All chat CTAs render through it.

**Inputs (via `@include(..., [...])`):**
- `message` (string, optional) — the prefilled chat text; url-encoded into `?text=`.
- `label` (string) — visible button text (e.g. "Ask free on WhatsApp →").
- `variant` (string: `primary` | `ghost` | `floating`) — styling; default `primary`.

**Behaviour:**
- Builds `href` = `https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}` + (message ? `?text=` . urlencode(message) : '').
- `target="_blank" rel="noopener"`.
- Renders the WhatsApp glyph inline (svg-symbols has none) + `{{ $label }}`.
- Styling keyed off `variant`; uses existing tokens (`#25D366` green, `--display`, radius). No new global CSS file — scoped styles live in the partials that need them (float carries its own; inline CTAs inherit page styles).

**Why a partial, not a PHP helper:** avoids a `composer.json` autoload-files change + `composer dump-autoload` on deploy. Tested via rendered HTML (feature tests), not a unit test.

---

## 4. Component: `partials.wa-float` (site-wide floating button)

- Fixed bottom-right; desktop = pill ("Chat to a real UK person" + glyph), mobile = circular FAB (glyph only, aria-label set).
- Renders via `wa-cta` with `variant=floating`, message = generic ("Hi Beyond Passports — I'd like some help with my trip.").
- Self-contained scoped `<style>` inside the partial (position, z-index, responsive). **z-index below the checklist sticky action bar** (that bar is `z-index:40`/`60` on mobile) — float sits at `z-index:35` so it never covers the checklist's bottom bar on mobile; on desktop both coexist (bar is top-sticky, float is bottom-right).
- **Includes:**
  - `resources/views/layouts/public.blade.php` — once, before `@include('partials.site-footer')`. Covers every page using the layout.
  - `resources/views/public/track.blade.php` and `resources/views/public/checklist-result.blade.php` — the two standalone pages that don't extend the layout. Add the include before their `</main>`/footer.
- Appears on all public pages including `/apply` and checkout result (per decision). Mobile z-index ordering ensures it doesn't obscure primary CTAs.

---

## 5. Component: per-destination chat CTA

- In `resources/views/destinations/show.blade.php`, near the existing apply CTA band: a `wa-cta` (`variant=primary` or `ghost`) with
  `message = "Hi Beyond Passports — I'd like help with my documents for {{ $destination->name }}."`
- Framing: the low-commitment alternative to applying — "Not ready to apply? Check your {country} documents with a real person."
- Uses the destination already in scope on that view (`$destination`).

---

## 6. Testing (Pest/PHPUnit feature tests)

- **wa-cta render:** a test view/route renders `wa-cta` with a message → HTML contains `wa.me/` + the url-encoded message + the label. (Assert via a page that uses it.)
- **Floating button presence:** GET home, a destination money page, `/document-checklist`, `/track/...` (or track form page), `/tools` → response contains the float partial marker (e.g. `data-wa-float` / `wa-float` class). One assertion per representative page.
- **Per-destination CTA:** GET a destination money page → HTML contains the destination name inside a `wa.me` `text=` param (url-encoded), proving the contextual message renders.
- **No-number safety:** with `config(['ukv.whatsapp' => ''])`, the link still renders against the placeholder fallback (no crash). (Documents current "show anyway" decision.)

---

## 7. Affected files

- Create: `resources/views/partials/wa-cta.blade.php`
- Create: `resources/views/partials/wa-float.blade.php`
- Modify: `resources/views/layouts/public.blade.php` (include float)
- Modify: `resources/views/public/track.blade.php` (include float)
- Modify: `resources/views/public/checklist-result.blade.php` (include float)
- Modify: `resources/views/destinations/show.blade.php` (per-destination CTA)
- Tests: `tests/Feature/ChatCtaTest.php`

---

## 8. Out-of-scope / follow-ups

- **Tools hub → "Free help"** (#350): add document-checklist + find-a-centre to the Tools menu/page + a chat block. Deferred to a later round.
- **Migrate the ~16 inline `wa.me` links** to `wa-cta` opportunistically.
- **Real WhatsApp number** (#339) — flips all CTAs live when set.
