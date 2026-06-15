# Coded rebuild (drop WordPress) — Laravel + MySQL design

**Date:** 2026-06-15
**Decision:** Replace the entire WordPress build with a coded stack. Full feature parity before launch. Public front stays the coded static pages. Backend + ops engine rebuilt in **Laravel + MySQL**, with **Filament** for the back-office. Portable to any host (cPanel shared / VPS / own server). WordPress retired once parity is reached.

## Why this stack (decision record)
- **Not WordPress** — satisfies the drop-WP goal; Laravel is a modern MVC framework.
- **Maximum portability** — PHP + MySQL runs on virtually every host, including cheap cPanel shared and the user's own server. No platform lock-in (unlike Netlify Functions + Supabase).
- **Reuse, not rewrite** — the existing mu-plugin business logic (eligibility router, pricing/quote, order meta conventions, email content, recipes) is plain PHP and ports into Laravel services, cutting the parity effort.
- **Filament** — Laravel's admin panel framework delivers the ops engine (orders hub, kanban, dashboards, CRUD, exports) far faster than hand-building, replacing wp-admin.
- Rejected: Next.js+Postgres (full rewrite, no PHP reuse, no shared-host support); Static+Netlify Functions+Supabase (platform-locked, hard to move to own server).

## Topology
- **Public site:** coded static pages (`frontend/`) on Netlify now. Dynamic pages (destination money pages, blog, track) become Laravel **Blade** views once the backend exists, or stay static + call the API. Front can later be served from the same Laravel box for a single-server deploy.
- **App + ops:** Laravel application (`app.` subdomain or same box) — apply funnel, payments, customer status, document handling, and the Filament back-office.
- **Seam:** coded apply/track either become Blade pages in Laravel, or stay static and POST to Laravel API routes. Stripe hosted Checkout takes payment (no card data on our servers). Stripe webhook → order state → emails/CRM/tracker.

## Domain model (Eloquent + migrations) — ported from mu-plugin meta
- `destinations` (from Pods `destination`): name, type (evisa/eta/visa-free), fees, validity, processing, required_docs, passport_validity_months, supply nodes.
- `orders`: ref (`UKV-YYYY-NNNNNN`), customer, destination, tier, amounts (service fee vs govt fee), status/stage, eligibility lane, govt reference, govt-fee-paid, ownership, SLA timestamps, passport_expiry, travel_date, consent, payer/applicant, minor/guardian, prior_refusal, dual_nationality, etc. (mirror `ukv_*` meta keys → typed columns).
- `order_events` (journey log), `barriers`, `client_updates`, `documents`, `appointments`, `supply_nodes`, `discounts`, `rejections`, `feedback`, `quotes`, `users` (ops/roles).
- Naming: preserve the meta-key semantics documented in memory (order = `ukv_`-prefixed concept, barrier bare) but as proper columns/relations.

## Services (port the tested PHP)
- `EligibilityService` — `evaluate()`, lane routing (standard vs manual_review), clearance gate. (from ukv-eligibility.php)
- `PricingService` / `QuoteService` — fixed tiers + bespoke quote + Stripe Payment Link. (ukv-quote.php)
- `OrderService` — create/transition with stage gates. (order-groups, stage-gates)
- `EmailService` — 9 lifecycle events as queued Mailables. (ukv-emails.php)
- `RetentionService` — GDPR doc auto-purge (scheduled). (ukv-retention.php)
- `ReconciliationService` — Stripe↔orders daily (scheduled). (ukv-reconcile.php)
- `CrmService` — HubSpot sync + timeline notes. (ukv-zapier/quicknote)
- `AiService` — Anthropic: next-best-action, doc review (vision), content drafting; guidance-redacted, leak-gated. (ukv-ai/doc-review)
- `SopService` — 9-lens recipes per stage. (ukv-sop)

## Phases (full parity before launch, but built in dependency order)

**L0 — Foundation**
Scaffold Laravel + Pest tests + MySQL + env/secrets; Fortify auth + roles; install Filament; core migrations + models; port + unit-test EligibilityService + PricingService.

**L1 — Customer path**
Apply intake (validation + eligibility routing + order create); Stripe Checkout (tiers) + webhook + manual-review quote/Payment Link; confirmation + ref; lifecycle emails (queue); document upload + retention purge; public status tracker; wire coded front (apply/track) to Laravel.

**L2 — Ops engine (Filament)**
Orders hub (completeness/auto-chase/SLA/priority); production-line kanban + stage gates + per-stage SOP; eligibility clear/refer + gate; barrier register + proactive client updates; supply-chain registry + required-docs/passport-validity + QA gate; appointments + premium slot + passport-return; group/linked orders + loyalty/fast-track + review-incentive; refunds/cancellation (+14-day) + rejection taxonomy + outcome→requirements loop; owner digest + reports/CSV + insights dashboard; Stripe reconciliation.

**L3 — Intelligence + integrations**
HubSpot sync + quick-note; AI assist (NBA, doc-review vision, content) leak-gated; optional Drive/Sheet feed; optional Zapier hooks.

**L4 — Content + SEO + dynamic public**
Destinations rendered from DB (money pages); blog/stories (anonymised) + consented testimonials; SEO (schema, sitemap, meta — spatie/laravel-sitemap); comparisons + hubs.

**L5 — Launch**
Security hardening (2FA, rate-limit, validation, secrets, headers); data migration from WP (or seed); hosting decision + deploy (front Netlify, Laravel on host) + domain + SSL; full regression + launch checklist; decommission WP.

## Cross-cutting
- **Test-first** (Pest), mirroring the discipline used for the mu-plugins.
- **Queue** (database/redis) for emails/AI/jobs; **Scheduler** (cron) for retention, reconciliation, owner digest, SLA checks.
- **Secrets** in `.env` (Stripe, HubSpot, Anthropic, mail) — never committed; ROTATE the live HubSpot token at cutover.
- **Compliance preserved:** not a government website; service fee separate from government fee; express = our handling not the decision; no approval guarantee; IDP = guided self-service; AI never receives passport numbers/scans without redaction; content engines draft-only + leak-gated.

## Risks
- **Scope:** full parity is large; L0–L1 give a launchable core, L2 reaches ops parity — sequence matters even though all are pre-launch.
- **Reuse limits:** WP-specific code (Forminator, Pods, Elementor, kit) does NOT port — only the plain-PHP domain logic does. Front re-wiring + admin are new builds (Filament accelerates).
- **Throwaway:** the WP theme-port tasks (#146, #151–153) and Elementor build tasks (#59–63) are now obsolete — cancel them.
- **Data:** if no real orders exist yet (pre-launch), skip migration and seed fresh; only destinations + content need carrying over.

## Status
Design approved in brainstorming (drop WP fully · full parity · Laravel+MySQL · portable · Netlify front for now). Next: writing-plans for L0, then build phase by phase.
