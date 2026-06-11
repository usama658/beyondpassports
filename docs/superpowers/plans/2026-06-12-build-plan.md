# Master Build Plan — UK Outbound Visa Site (plugin-first Travisa)

> Reflects the real stack (local WP + Travisa Elementor kit + Pods/Forminator/HFE/RankMath/Pipedrive). Supersedes the 6 older custom-code plans. Follow `docs/superpowers/RULES.md`. Execute phase-by-phase; verify each task.

**Environment:** local XAMPP — `http://localhost/ukvisa`, DB `ukvisadb`, wp-cli `C:\xampp\wp-cli.phar`, admin `admin`/`ukvisa-admin-2026`. Run wp-cli via Bash; UI via Playwright (`automation/`).

---

## Phase 0 — Environment & shell ✅ DONE
- WP installed; Travisa Elementor kit imported (Template Kit Import via Playwright), deduped to one-per-title (session 155); Hello Elementor base.
- Plugins active: Elementor, Jeg Elementor Kit, MetForm, HFE, Pods, Forminator, RankMath.
- Core pages published from kit templates: Home (front page), Visa Services, Pricing, FAQ, About, Contact. Primary nav assigned. Pretty permalinks (`.htaccess`).
- **Done check:** all pages HTTP 200; Home renders Travisa design with header/footer.

## Phase 1 — Brand & global design
- Apply direction-A palette + Inter + button style to the **global Kit Styles** (Elementor Site Settings → Global Colors/Fonts). Gold = Premium only.
- Confirm/assign Header + Footer site-wide (HFE or Jeg theme builder); add the "not a government website" disclaimer strip (header + footer).
- Add logo (placeholder wordmark until brand asset supplied).
- **Done check:** sample button renders `#1456B8`/6px/Inter; disclaimer present top+footer on every page; Lighthouse mobile ≥90 on Home.

## Phase 2 — Data model (Pods, single source of truth)
- Create Pods CPT **Destination** (`destination`) with stable field keys: `required_for_uk`(bool), `visa_type`(eVisa/eTA/visa-free/…), `max_stay_days`, `validity_days`, `entry`, `govt_fee_gbp`, `processing_standard_days`, `processing_express_hours`, `requirements`(repeatable), `how_to_steps`(repeatable), `tier_standard_gbp`/`tier_express_gbp`/`tier_premium_gbp`, `idp_recommended`(bool), `idp_permit_type`, `idp_required_photocard`(bool), `idp_required_paper`(bool), `notes`.
- Seed 8 destinations from the JSON reference (`wordpress/hello-child/data/visas/*.json`): Turkey, Egypt, India, Morocco, UAE, Australia, USA, Schengen. **Verify each value against the official source before launch** (data-QA).
- Export Pods config to git (`wordpress/pods-config.json`).
- **Done check:** 8 Destination entries exist; a Pods template/shortcode renders Turkey's requirements + fees correctly.

## Phase 3 — Money pages (content silos)
- Bind the **Visa Detail** kit template to render Destination data dynamically (Pods magic tags / Pods block inside the Elementor template, or a Pods template embedded). Build **Turkey first** (canonical), then replicate per destination (8 pages, slugs `/turkey/` … `/schengen/`).
- Visa-free destinations (Morocco, UAE, Schengen): show "no visa needed", hide fee/apply, show IDP/driving cross-sell.
- Add FAQ (FAQPage) + HowTo + Service + Breadcrumb schema via RankMath per page.
- Support guides: one per silo at launch (answer-first, Article schema, silo-linked).
- **Done check:** 8 money pages HTTP 200, render Pods data (edit a Pods value → page updates), schema valid in Rich Results, ≤1 exact-match anchor, guides link within silo only.

## Phase 4 — Tools (Forminator)
- **Visa checker** (`/do-i-need-a-visa/`): Forminator quiz/form — nationality + destination → result from Destination data (via glue snippet so it's not duplicated). British authoritative; other nationalities = generic fallback; CTA → apply.
- **IDP checker** (in the IDP hub): destination + **licence type (photocard/paper)** → correct convention + honest PayPoint in-person how-to + locator link.
- Photo maker: **deferred** (embed/third-party later).
- **Done check:** checker returns correct result for British across 8 dests; IDP checker correct for France+photocard (no), France+paper (1968), USA (1949); no data duplicated (reads Pods).

## Phase 5 — Application funnel + payments (Forminator)
- Multi-step Forminator form on `/apply/` (noindex): destination → tier (Standard/Express/Premium) → applicant details → document upload → review & pay. Conditional pricing from Pods (glue): service tier + govt fee at cost; visa-free = non-orderable; IDP = cross-sell link only.
- Stripe (Forminator Stripe addon, test mode first); capture passport number; success → confirmation + GA4 `purchase`.
- **Done check:** test-mode payment completes with correct total from Pods; passport number captured; GA4 purchase fires; `/apply` is noindex.

## Phase 6 — CRM + ops (Pipedrive + Zapier)
- Forminator submission → Zapier → Pipedrive deal in the "Visa fulfilment" pipeline (stages: Paid → Awaiting docs → Doc review → Submitted → Awaiting decision → Delivered → Won; Rejected/Refunded branches).
- Stage-driven transactional emails (eVisa PDF vs ETA "linked to passport" wording differ); refund sliding-scale rule; encrypted doc links + retention.
- **Done check:** test payment creates a deal with full payload (incl. passport number); each stage fires correct email; refund math matches the rule.

## Phase 7 — IDP / driving-abroad silo
- Hub `/international-driving-permit/` (rank "international driving permit uk") + how-to/service spoke (how-to-get/cost/1949-vs-1968/online-honest) + destination spoke `/driving-in-<country>/` (merge "driving in X requirements" + IDP need by licence). Data from Pods `idp_*` + an IDP-conventions extension.
- Cross-link money pages' IDP card → `/driving-in-<dest>/`; PayPoint locator link; reuse compliant photo guidance.
- **Done check:** hub + ≥3 driving pages live with schema; France page states the photocard exemption; money-page IDP links resolve (no 404).

## Phase 8 — SEO, compliance, analytics, launch
- Legal/trust pages: how-it-works, pricing (skeleton), refunds, terms, privacy, about — with disclaimer + GDPR/ICO content.
- RankMath: titles, Org + WebSite schema, breadcrumbs, sitemap → GSC; robots; `/apply` noindex.
- Analytics: GA4 + GSC + Microsoft Clarity (+ cookie consent gating); Looker data sources.
- Performance pass (caching/images/CWV); accessibility pass.
- **Migrate local → production host** (export DB + wp-content; update URLs; SSL; canonical redirect); re-verify acceptance on prod.
- **Done check:** all legal routes 200 + disclaimer; schema valid; analytics recording; Lighthouse ≥90; production live over HTTPS.

---

## Build order & dependencies
0 (done) → 1 → 2 → 3 → (4 ∥ 5) → 6 → 7 → 8. Phase 2 (Pods) gates 3/4/5; the glue snippet is introduced in 4/5 (brainstorm first). IDP (7) closes the money-page cross-sell loop.

## Cross-refs (still-valid detail in older specs)
- Silo blueprint, anchor/schema detail: `2026-06-11-content-silos-design.md` (data now in Pods, not JSON-shortcodes).
- Funnel steps/pricing/refund logic: `2026-06-11-apply-funnel-design.md` + `2026-06-11-crm-ops-design.md` (implemented in Forminator/Pipedrive, not custom JS).
- IDP facts/silo: `2026-06-11-idp-driving-abroad-design.md` + memories `idp-paypoint-self-service`, `express-not-faster-govt`, `plugin-first-architecture`.
