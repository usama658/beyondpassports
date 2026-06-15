# Guide Engine + SEO Silo — design spec

**Goal:** organic acquisition. Win top-of-funnel informational queries ("do UK citizens need a visa for X", "X eVisa documents", "X passport rules", "how long does X take") with a hybrid country-cluster silo that funnels readers to the money pages + /apply. Content must be factual (visa = YMYL), kept current, and user-verifiable.

**Decision:** DB-driven guide engine (approach B). Requirements/guides are DATA, not code. Approaches A (static PHP registry, breaks at scale) and C (programmatic thin pages → Helpful-Content demotion) were rejected.

## Architecture (one engine + 3 layers + 3 modules)

### Engine — DB-driven guides
- `guides` table: `destination_id` (FK nullable = evergreen), `guide_type` (enum, null for evergreen), `slug`, `title`, `excerpt`, `quick_answer`, `body` (longtext HTML), `meta_title`, `meta_description`, `faq` (json Q/A), `status` (draft|published, default draft), `published_at`, `reviewed_by`, `reviewed_at`, `sort_order`, timestamps. Unique `(destination_id, guide_type)` for country guides; slug index.
- `Guide belongsTo Destination`; `Destination hasMany guides`.
- PHP `GuideType` enum, 15 cases: do_i_need_visa, documents, passport_validity, processing_time, how_to_apply, cost_fees, when_to_apply, children, refused, residents, transit, visa_on_arrival, entries, driving, health. Each exposes `label()` + `topicSlug()` (e.g. do_i_need_visa → `do-i-need-a-visa`).

### Routing & resolution
- `/visa/{destination}` — existing money page = hub; now auto-lists its published cluster (cards w/ excerpt + read-time).
- `/visa/{destination}/{topic}` — resolve destination slug + guide_type by topic → published guide or 404. `{topic}` route-constrained to the 15 known topic slugs.
- `/guides` + `/guides/{slug}` — evergreen (destination_id null) retained.
- Legacy 6 registry slugs → 301-redirect to new homes; retire `const GUIDES`.

### Layer — SEO/quality
- Single `guides/show` renders any guide from fields.
- JSON-LD: Article + FAQPage (from `faq`) + BreadcrumbList; HowTo on the how_to_apply type. All `@@`-escaped in Blade.
- Auto hub-and-spoke links: breadcrumb UP to country hub; "more {country} guides" ACROSS to siblings; CTA DOWN to /apply?destination={slug} + the money page + the /document-checklist tool.
- E-E-A-T: visible "Reviewed {date} by {reviewer}" byline + author/reviewer in schema.
- The **documents** guide type embeds the LIVE requirements checklist (RequirementService::preview) instead of static prose — never stale.

### Layer — compliance
- Mandatory per-type disclaimer blocks baked into templates: service-fee-separate, not-a-government-site, no-approval-guarantee (esp. cost_fees type).
- Publish gate blocks unless disclaimers present.

### Layer — factuality
- Facts come from structured data (destinations + requirements engine), never AI invention. AI drafts prose AROUND injected ground-truth facts; prompt forbids stating any unprovided figure.
- No-invention validator: scan body for £-amounts / day-counts / dates NOT in the injected fact set → flag for reviewer.
- Citations: per-destination `sources` map; each factual claim links the official source (gov.uk / issuing authority).
- Human verify gate: publish blocked until reviewer ticks "facts verified vs official source" + name recorded (the E-E-A-T byline).

### Module A — public self-verification
- Inline "Source: gov.uk →" links on guides + money pages (from `sources`).
- Trust strip on every guide + money page: "Facts last reviewed {date} — confirm current rules at the official source →".

### Module B — freshness system
- `facts_checked_at` + `review_interval_days` per destination.
- Daily scheduled command flags overdue destinations → owner digest (#94) "data due for review".
- Filament "Data review" action per destination: edit fees/times/docs/validity/sources → tick verified → bumps `facts_checked_at` + re-stamps that country's guides' reviewed date.

### Module C — AI change-detection (automated #138)
- `data_change_proposals` table: destination_id, field, current_value, proposed_value, source_url, model_summary, status (open|accepted|dismissed), resolved_by/at.
- Weekly command `destinations:check-changes`: WebFetch each official source page (public gov data — zero customer data, leak-safe) → model flags differences vs stored values as structured {field, current, proposed, evidence} → create `open` proposals (dedup against existing open).
- Filament "Data changes" inbox + owner digest: Accept (writes to destination, bumps facts_checked_at, re-stamps guides) or Dismiss. NEVER auto-applies.
- Guardrails: fetch failure logged not fatal; rate-limited; model flags only, human decides.

### Hubs · sitemap · migration
- Sitemap: published guides (nested + evergreen), lastmod from published/updated.
- `/guides` index lists evergreen + links country hubs.
- Data migration: 6 legacy registry guides → rows (+301s).

### Content workflow
- AiService drafts a guide body → `draft`. Filament `GuideResource`: list filtered by destination/type/status, rich-text edit, FAQ repeater, Publish action. AI draft via per-row Filament button + a batch artisan command. Drafts 404 publicly (staff preview allowed).

## Build waves (parallel agents per wave)
1. Engine: migration + Guide model + GuideType enum + routing + show/hub templates + schema + migrate legacy.
2. Layers: factuality validator + compliance blocks + E-E-A-T + live-docs embed + Filament GuideResource + AI draft.
3. Modules: A (verify links/strip), B (freshness), C (change-detection inbox + command).

## Scope note
First build delivers the engine + 1 worked Turkey cluster (15 types, AI-drafted + reviewed). Remaining 7×15 = content production via the pipeline (draft→review), not code. Factual accuracy ultimately depends on verified destination data (#96/#129, owner-gated) — the engine guarantees the process; verified data guarantees the facts.

## Testing
Nested + evergreen routing; draft 404 / published 200; hub lists published-only; schema + breadcrumb present & escaped; sitemap published-only; publish-gate rejects thin/uncited/unverified; legacy slugs redirect; no-invention validator flags planted bad number; change-detection creates a proposal from a diffed fixture; accept applies + re-stamps.
