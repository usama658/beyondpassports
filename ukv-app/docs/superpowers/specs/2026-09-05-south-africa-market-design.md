# South Africa market launch — design spec

**Goal:** open Beyond Passports' Schengen-visa-assistance service to South-Africa-based applicants. Phase 1 of a broader source-market expansion (USA, other African countries planned later — NOT this spec). Deliberate growth bet, no existing SA demand signal — full build chosen anyway (LP + ZAR pricing + live SA appointment-sweep ops), not a lean validation-first launch.

**Decisions (locked):**
- New domain `beyondpassports.com` (already owned) hosts international/SA pages. `beyondpassports.co.uk` stays UK-only. No host-based market-detection code — DNS points `.com` at the same app; a plain route (`/south-africa`) is reachable on either domain. Market is chosen by URL, not by inspecting the Host header.
- SA appointment data is a first-class parallel dataset to UK's, not merged into it: existing `/schengen-visa` UK board must show zero SA centres and vice versa.
- SA compliance (ASATA/FICA-style local licensing for travel/visa facilitation) is an **open, unresolved risk** — documented, not blocking this build, but must be resolved before real ad spend goes live.
- ZAR pricing is FX-converted from the current £130 UK fee, but the actual number is `verify` (placeholder) until a live rate is pulled at implementation time — never fabricate a figure, same governance rule as `country-logic.md`'s `verify` fields (ASA CAP 3.28).

**Out of scope:** USA, other African countries, any generalized multi-market abstraction beyond what SA needs, host-based market-detection middleware, migrating `.co.uk` to `.com`.

## Routing & landing page

- New route `GET /south-africa` → new `SaLandingController@show` (or equivalent), registered normally (no `Route::domain` constraint) — works identically whichever domain resolves to the app.
- New Blade view, NOT the static `lp-v2-preview.html` (that file is UK-only, hardcodes GBP + UK copy via string literals). Reuses the existing locked `partials.lp-chrome` (topbar+header) and `lp-footer` partials (bpc- prefixed) per the LP-chrome pattern already used by the 6 standalone lp-* pages.
- Copy, pricing, and WhatsApp CTA are pulled from `config('ukv.markets.za')` — never hardcoded inline in the view, matching the existing `ukv.pricing` / `ukv.stats` pattern.
- WhatsApp CTA reuses the existing numbers (WhatsApp is not country-gated); lead capture tags the source as `za` so ops can distinguish SA leads in the sheet.
- Disclaimer strip: reuse the existing locked `disclaimer-strip` partial (light/dark variant), never bare markup — per [[disclaimer-strip-partial]].

## Config

New `config/ukv.php` key `markets.za`:
```php
'markets' => [
    'za' => [
        'label' => 'South Africa',
        'currency' => 'ZAR',
        'price_total' => 'verify',   // FX-convert £130 at implementation time
        'price_upfront' => 'verify',
        'price_remainder' => 'verify',
        'whatsapp' => null,          // null = fall back to config('ukv.whatsapp')
    ],
],
```

## Data model

- `supply_nodes`: add nullable `market` column (string, e.g. `uk` / `za`). Migration backfills every existing row to `'uk'` — zero behavior change for the current UK board.
- New SA `supply_nodes` rows for VFS Global South Africa VACs (Pretoria/Johannesburg, Cape Town, Durban — confirm exact operator/cities at implementation time; SA is VFS Global per-Schengen-country same as UK, separate account per country per existing sweep-rule pattern), `market='za'`, mapped via the existing `destination_supply_node` pivot to the same `destinations` rows (Schengen countries) UK already uses. Do NOT create a parallel destinations table — same 29 Schengen countries, different supplying VACs.
- `postcodes.io` geocoding (used by the nearest-centre finder) is UK-only — do not attempt to geocode SA addresses through it. Enter SA `lat`/`lng` manually (small, fixed set of cities) if the nearest-centre finder is wanted on the SA page; otherwise leave null and skip that feature for SA launch.

## AvailabilityService

- `byDestination(string $visaType = 'Schengen', string $market = 'uk')` gains the `$market` param.
- Both the destination-scoped `supplyNodes` query and the `is_global` query add `->where('market', $market)`.
- Existing callers (UK `/schengen-visa` board) get the default `'uk'` — no call-site changes required, confirming the backfill is safe.
- New test: `byDestination('Schengen', 'za')` returns only `market='za'` snapshots; `byDestination('Schengen', 'uk')` (or default) is unaffected by SA rows existing in the table.

## Admin

- "Update Availability" paste-block screen (Filament) gets a market selector (default `uk`) so ops can target ZA rows without touching the UK ones. Same paste format (`<slug>: YYYY-MM-DD good|limited` or `<slug>: ask`), scoped by the selected market.

## Ops (outside this codebase change — infra/manual)

- New SA VFS Global portal accounts (per Schengen country, mirrors UK's one-account-per-country model) — credential setup is a manual task for the user, not code.
- New Google Sheet tabs "SA Availability" and "SA Portal Accounts" mirroring the UK sheet's structure ([[appointment-availability-flow]]).
- Same one-check-per-country / rate-limit discipline as the UK sweep (VFS Global is the same platform — 429201/429001 rules likely apply identically, to be confirmed on first real SA sweep).
- A `docs/appointment-availability-playbook-za.md` (or a ZA section appended to the existing playbook) gets written once real SA portal accounts exist — not before, to avoid documenting invented steps.
- DNS: point `beyondpassports.com` at the same hosting target as `beyondpassports.co.uk` — user/infra action, requires explicit go-ahead before executing (no-auto-deploy: any DNS/hosting change goes live immediately).

## Testing

- Regression: existing `CentreAvailabilityTest` / UK board output is unchanged after the `market` column + backfill migration.
- New: `AvailabilityService::byDestination` market filtering (SA rows never leak into UK results, UK rows never leak into SA results).
- Feature test: `/south-africa` renders ZAR price from config, correct hero copy, correct WhatsApp CTA, disclaimer strip present.

## Compliance

- SA travel/visa-facilitation licensing (ASATA membership, FICA obligations, or equivalent) is **unchecked**. Flagged as an open risk here; must be resolved before the page goes live to paid traffic. Does not block building the page/ops behind a not-yet-advertised URL.
- Bundling any insurance/product sale for SA clients inherits the same FCA-introducer-vs-in-bundle-sale question already open for UK ([[schengen-service-pricing]]) — re-check for SA-specific equivalents (FSCA) before selling insurance to SA clients.
