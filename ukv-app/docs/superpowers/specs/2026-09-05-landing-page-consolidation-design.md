# Landing Page Consolidation — Design Spec

**Date:** 2026-09-05
**Status:** Approved by owner, pending implementation plan.
**Scope:** Information architecture for beyondpassports.co.uk's paid-landing-page system. First of three planned sub-projects (IA → funnel/CTA → visual), per the brainstorming session that produced this spec. Funnel and visual work are explicitly out of scope here and get their own spec/plan cycles once this ships.

## Problem

The site has accumulated 13 distinct paid-landing-page URLs across two parallel systems:

- **8 hand-built one-off templates** (`lp-speed`, `lp-speed-original`, `lp-fear`, `lp-appointments`, `lp-trust`, `lp-refused`, `lp-bold`, `lp-agency`), each its own blade file, its own copy, its own maintenance burden.
- **5 config-driven variants** on a shared `lp-v2` template via `LpVariantController` (`services-uk`, `assistance`, `application-help`, `agents-uk`, `consultancy`) — the newer, cleaner pattern. One of the old templates (`lp-bold`) was already retired onto this pattern on 2026-09-04 (the `consultancy` variant).

This is genuine, real sprawl: two different ways to build the same kind of page, several near-duplicates (`lp-speed` vs `lp-speed-original`), and at least one already-dead page (`lp-bold`, superseded but its route/file still exist).

No Google Ads/GA4 performance data was available to inform this decision (Search Console API is disabled on the available Google Cloud project, and no Ads/GA4 credential exists in this environment) — this consolidation is based on copy/angle analysis and the fact that the codebase was already mid-migration toward the `lp-v2` pattern, not on conversion data. If real per-page performance data becomes available later, revisit before finalizing which angles ship.

## Decision

**Finish the migration already in progress.** Fold the remaining distinct psychological angles from the 8 hand-built templates into the `LpVariantController`/`lp-v2` config pattern, alongside the existing 5 keyword-matched variants. One template, one design system, angle and keyword both become config entries, not separate blade files.

### Target variant set (10 total, down from 13 pages across 2 systems)

**Keep as-is (already on the good pattern):**
- `services-uk` — "Schengen Visa Services UK"
- `assistance` — "Schengen Visa Assistance UK"
- `application-help` — "Schengen Visa Application Help & Support UK"
- `agents-uk` — "Schengen Visa Agents UK"
- `consultancy` — "Schengen Visa Consultants UK" (current default/flagship)

**Migrate onto the pattern (new config entries, one per angle):**
- `speed` — from `lp-speed` ("submitted in 5 working days"). Drop `lp-speed-original` entirely — it's an explicitly-labelled "kept on request" duplicate of `lp-speed`, no distinct angle.
- `fear` — from `lp-fear` ("a refusal stays on your record for 5 years")
- `appointment-blame` — from `lp-appointments` ("your visa is not slow, your consulate is")
- `trust` — from `lp-trust` ("verify us before you trust us")
- `refusal-recovery` — from `lp-refused` ("the reason they gave you is probably not the real reason")

**Drop entirely:**
- `lp-agency` (`/schengen-visa-agency`) — niche framing, no meaningfully distinct angle from `agents-uk`.
- `lp-bold` (`/schengen-visa-consultancy-old`) — already superseded, route/file are dead weight.
- `lp-speed-original` (`/schengen-visa-agent-premium`) — see above.

### Out of scope (unchanged by this spec)

- **Core/evergreen pages** — `/`, `/about`, `/legal`, `/schengen-visa` (destination hub), `/visa/{country}`, `/guides`, `/document-checklist`, `/find-a-centre`, `/reviews`, `/compare`. These are distinct, real pages with distinct jobs, not sprawl.
- **Market/country-specific pages** — `/visa/france`, `/south-africa`. Different axis entirely: these vary by *destination country* or *source market*, not by persona/angle. They stay on their own pattern.
  - **South Africa note:** `/south-africa`'s current placeholder copy leads with appointment-availability framing (mirroring the UK's core positioning). Real SA demand data gathered this session (SEMrush, ZA database) shows this is backwards for that market: "schengen visa appointment" gets only 70/mo in South Africa vs 2,400/mo for "schengen visa application" (South Africa is a top-4 global market for that exact phrase). When `/south-africa` gets its real copy pass (a separate task, blocked on SA compliance/pricing decisions per the SA market design spec), it should structurally mirror the `application-help`/`assistance` variant's approach, adapted for SA pricing/currency, not the appointment-led angle it currently has. This is a content-direction note for that future task, not something this spec builds.
- **Funnel/CTA structure and visual/brand redesign** — separate specs, sequenced after this one per the brainstorming session's agreed order (IA → funnel → visual).

## Architecture

No new architecture. `LpVariantController` and the `lp-v2` blade template already support this — the work is:
1. For each migrating angle, extract its current hand-built copy into a new `config('ukv.lp_variants.<key>')` entry, matching the existing entry shape (title, meta, h1_lead, h1_gold, hook, sub — see existing `assistance`/`consultancy` entries in `config/ukv.php` for the shape).
2. Add a route for each new variant key via `LpVariantController::show` with `->defaults('variant', '<key>')`, at the same URL the old hand-built page used (preserve existing inbound ad links/URLs — this is a copy/template migration, not a URL change).
3. 301-redirect or remove the old hand-built routes/blade files once each variant is confirmed matching (`lp-speed`, `lp-fear`, `lp-appointments`, `lp-trust`, `lp-refused` routes currently point at `pageOrCoded(..., 'public.lp-*')` — these become `LpVariantController::show` with a `defaults('variant', ...)` instead, same URL).
4. Delete the dropped pages' routes and blade files (`lp-agency`, `lp-bold`, `lp-speed-original`) — confirm no live ad campaigns still point at these URLs before removing (this is a real-world check, not a code check).

## Testing

Existing pattern: each `LpVariantController` variant should get the same test coverage the existing 5 variants presumably already have (route resolves, correct copy renders, disclaimer strip present). Migrated pages get a golden-content check against their current live copy to confirm nothing changed during the migration, same spirit as `ContentPagesGoldenTest` used elsewhere in this codebase for CMS-vs-coded parity.

## Risks

- **Ad campaign URL dependency (real-world, not code):** before deleting `lp-agency`/`lp-bold`/`lp-speed-original`, confirm no live Google Ads campaign still targets those exact URLs — this needs a human check in the Ads account, not something verifiable from the codebase.
- **Copy drift during migration:** moving copy from a hand-built blade file into a config array risks losing formatting/emphasis that was hardcoded in HTML. Each migrated variant should be visually diffed against its current live page before the old page is removed.
