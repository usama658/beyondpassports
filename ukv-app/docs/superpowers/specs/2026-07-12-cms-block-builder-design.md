# CMS Block Builder — Design Spec

**Date:** 2026-07-12
**Project:** Beyond Passports (Laravel 11 + Filament v3, warm-light petrol/teal theme)
**Status:** Approved shape, pending final spec review before implementation plan.

## 1. Goal

Give the non-developer team a way to edit and reorder public site content (page sections, copy, images, links, order, SEO) from the existing Filament admin, **without touching Blade and without any risk to the theme, layout, functionality, or Core Web Vitals**. Developer (code) workflow stays exactly as it is today.

## 2. Users

- **Editor** (team, non-dev): edits content only — words, images, section order, links, per-page SEO, site settings, nav, articles. No access to orders, customers, payments, refunds, or code-level styling.
- **Admin** (owner/dev): everything, plus manages pages/toggles and adds new block types in code.

## 3. Scope

**In scope:** a lightweight, theme-safe block page-builder for **marketing/content pages**. Roll-out pilots **Home** first, then about / services / schengen-visa.

**Out of scope (stay coded):** functional/logic pages — apply, checkout, track, confirmation, documents — and legal pages. These carry payment/compliance logic and are never blockified. Interactive components (eligibility checker, apply form, slot modal) may appear as **locked include-blocks** (reorderable, not editable).

**Explicitly OUT (YAGNI / theme-safety):**
- Editing colours, fonts, spacing, or raw CSS/HTML in the CMS (single biggest theme-breaker — locked to code).
- A second blog engine — "Posts/Articles" **reuse the existing Guides engine** (guides table, GuideController, SEO, publish gate), surfaced to the Editor role.

## 4. Non-negotiable guarantees

### 4.1 Theme / layout / functionality compatibility
1. **Same markup.** Each block renders an existing Blade partial + existing `ukv.css` classes. The CMS only supplies the text/image already hardcoded today. Same HTML/CSS → pixel-identical output.
2. **Golden-master screenshot gate.** For every migrated section: extract to a shared partial, render with the *current* values, and screenshot-diff against the live page. Must be pixel-identical before it ships.
3. **Fields, not HTML.** Editors fill structured fields (headline, list items, image + alt, links). No raw HTML/CSS/class input. They change words and images only — never structure.
4. **Functional parts stay code.** Checker, apply form, slot modal, checkout never move into the CMS; at most they appear as locked include-blocks.
5. **Additive + reversible + fallback.** New tables + Editor role only. No existing route/controller/payment/apply/track code changes. A page with no published CMS content renders the existing coded view.
6. **Admin CSS isolated.** Filament styles load only on `/admin`. Public CSS is untouched. The builder ships zero bytes to public visitors.
7. **Test gate.** Pest suite + screenshot smoke + Lighthouse/CWV check of every migrated page must pass before deploy.

### 4.2 Performance / Core Web Vitals
- **No new front-end assets.** Same HTML/CSS/JS bundles as today. No new fonts/libraries. INP/TBT unchanged (zero new public JS).
- **Full-page render cache.** Published pages are cached as rendered HTML; the block loop runs once, not per request → TTFB same or faster, protecting LCP.
- **CLS protection.** Same markup + same CSS = same layout. Image blocks emit intrinsic `width`/`height`.
- **Media pipeline (the one real risk — team images).** On upload: compress, convert to WebP, cap max dimensions, store intrinsic dimensions, emit `loading="lazy"` + explicit `width`/`height`. Neutralises the LCP and CLS angles of oversized uploads.
- **Measured gate.** Lighthouse/PSI on Home before pilot and after; must not regress before deploy.

### 4.3 Reversibility (disconnect / reconnect)
- **Global kill switch:** `UKV_CMS_ENABLED` env flag. Off → every public route renders the current coded Blade; CMS ignored, no data touched.
- **Per-page toggle:** each page has `mode` = `coded` | `cms` (default `coded`). Flip one page to CMS, leave others coded; flip back anytime, live.
- **Auto-fallback:** even in `cms` mode, a page with no published blocks renders coded.
- **Shared-partial design (why revert is free):** coded Blade is NOT deleted. Each section becomes a shared partial used by **both** the coded page and the block renderer. CMS is an *alternate data source* feeding the same partials, not a replacement. The coded path always works.
- **Content preserved on disconnect:** turning CMS off never deletes pages/blocks; reconnecting restores exactly where you left off.
- **Revert ladder:** bad edit → revision revert; one page → toggle to coded; whole site → env flag; nuclear → `git revert` (tables sit unused).

## 5. Stack

Everything rides the existing app — no new stack, no second app, no headless CMS, no front-end framework.

- **Laravel 11** (existing)
- **Filament v3** — CMS is a new "Content" nav group in the current `/admin` (same login, 2FA, roles, deploy)
- **Native Filament Builder field** for the drag-drop block stack (ships with Filament — no package)
- **MySQL** — new tables below
- **Blade + `ukv.css`** for public rendering (existing partials)
- Rich text = Filament built-in RichEditor (no package). Media = Filament built-in FileUpload + a small `media` table (no Spatie/Curator for MVP).

**No new front-end dependencies. Deploy unchanged:** `git pull → migrate → view:cache`.

## 6. Data model

- **`pages`**: `id, slug, title, mode('coded'|'cms'), status('draft'|'published'), blocks(JSON), seo_title, seo_description, og_image, noindex(bool), in_sitemap(bool), published_at, timestamps`.
- **`page_revisions`**: `id, page_id, blocks(JSON snapshot), title, seo_*, editor_id, created_at`. Keep last ~10 per page; one-click revert.
- **`media`**: `id, path, disk, mime, width, height, alt, size, uploaded_by, timestamps` (optimised on upload).
- **`site_settings`**: singleton (key/value or single row) — logo, favicon, brand name, contact details, socials, WhatsApp, header CTA text, footer text, announcement bar, nav menu (JSON of {label, route|url, order, children}).

Blocks live as JSON on `pages.blocks` (and revisions). Each block = `{ type, data{} }`.

## 7. Block registry (the extension point)

- A `BlockRegistry` maps `type => [Filament schema, Blade partial]`. Adding a new section = 1 partial + 1 schema class registered here (~30 min), then it appears in the builder palette for all pages. Never touches existing blocks (isolated).
- **MVP block library** (from real sections): hero, trust-band, steps/process, destination-grid, quote/testimonial, faq, cta-band, rich-text, image, locked-component (includes an existing interactive component untouched).
- Any future coded design can be "promoted" to a block by wrapping its partial + declaring its fields.

## 8. Rendering + workflow

- **`CmsController` / route resolver:** if `UKV_CMS_ENABLED` and page `mode=cms` and has published blocks → render blocks (loop registry → `@include` each partial with its data) inside the site shell (header/footer). Else render the existing coded view. Result cached (full-page) and invalidated on publish.
- **Workflow:** Draft → **Preview** (renders the page live from the draft snapshot at a signed preview URL) → **Publish** (writes a revision, marks published, busts cache).
- **Pages are a stacked column of blocks** in the site shell; multi-column/special layouts live *inside* a block. Distinct page shells (e.g. header-less landing) = optional phase-5 "layout" toggle.

## 9. Access control

- New **Editor** role (Filament policy): access limited to the Content group — Pages, Media, Site Settings, Nav, Articles (Guides). Denied on Orders, Customers, Payments, Refunds, Supply, Slots, etc.
- Admin retains full access and is the only role that can change page `mode` and add block types (code).

## 10. Two-lane development model

- **Code lane (dev + Claude, unchanged, fast):** edit Blade partials, CSS, logic, build new sections, commit, deploy via git. Editing a shared partial updates both the coded fallback and the CMS output. No speed lost.
- **Content lane (team):** edit words/images/order/links/SEO in the admin (DB JSON).
- **No collision:** team content = DB; structure/design/CSS = code. Different layers; a team edit can't overwrite code and a code edit can't wipe content.

## 11. Phasing (each phase gated)

1. **Foundation** — migrations (`pages`, `page_revisions`, `media`, `site_settings`), `BlockRegistry`, native Builder field wiring, Editor role + policies, `CmsController` + route resolver + `UKV_CMS_ENABLED` flag + per-page `mode` + coded fallback, full-page cache scaffold. Gate: existing suite green; all public pages still render coded (flag off).
2. **Pilot: Home** — extract each Home section into a shared partial; build matching blocks; migrate current Home content into a `cms` Home page; draft/preview/publish. Gate: **pixel-identical golden-master screenshot diff** (coded vs cms Home) + Lighthouse no-regression + reversibility proven (flag/toggle both ways).
3. **Roll-out** — about / services / schengen-visa onto the same block library (same gates each).
4. **Settings + nav + media + SEO** — site settings singleton, nav menu builder, media pipeline (compress/WebP/dimensions/lazy), per-page SEO fields wired into `<head>` + sitemap.
5. **Reusable/global blocks + templates + revisions polish** — global blocks (edit once, reflect everywhere), page templates (clone a block stack), revision UI + one-click revert, optional page-shell/layout toggle.

## 12. Testing

- Pest: page render (coded vs cms parity), fallback when unpublished, flag off = coded, Editor role can't reach Orders/Payments, publish writes a revision, revert restores.
- Screenshot golden-master per migrated page (coded vs cms must match).
- Lighthouse/PSI CWV before/after on migrated pages; no regression.
- Full public-page screenshot smoke before each deploy.

## 13. Open items resolved

- Posts/Articles = existing Guides engine (no second blog). ✅
- Colours/fonts/CSS = OUT (code only). ✅
- Nav menu builder = IN (light, phase 4). ✅
- Full-page render cache + media pipeline + Lighthouse gate = IN (perf guarantees). ✅
- Scheduled publish, redirects manager, audit log = LATER.
