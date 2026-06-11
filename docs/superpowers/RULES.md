# Development & Design Rules — UK Outbound Visa Site

**Status:** authoritative. These rules override the older custom-code specs/plans where they conflict (those assumed Hello Elementor + bespoke shortcodes/JS; we pivoted to a plugin-first Travisa build).

## 1. Architecture rules (plugin-first)
1. **Plugins over custom code.** Use existing plugins for every feature. Do **not** write custom code without brainstorming + explicit approval first.
2. **The only sanctioned custom code** is one small **glue snippet** that feeds Pods data into Forminator (single source of truth). Even that must be brainstormed before writing.
3. **Single source of truth = Pods "Destination" CPT.** Per-country visa/IDP data lives there once. Money pages, checker, and funnel all read it — never hand-duplicate prices/requirements across tools.
4. **Stack (pinned, free tier):** Travisa FSE-kit visuals via **Elementor + Jeg Elementor Kit + MetForm + Header-Footer-Elementor (HFE)**; **Pods** (data); **Forminator** (funnel + checkers); **RankMath** (SEO/schema); **Pipedrive + Zapier** (CRM); Hello Elementor base theme. Photo maker **deferred**.
5. **Paid migration path** (JetEngine / Fluent Forms / Elementor Pro) is allowed later; keep Pods field keys stable + data values in the JSON reference so migration is low-friction. Forms would be rebuilt (no cross-plugin import).

## 2. Workflow rules
1. **Local → staging → production.** Build on local XAMPP (`C:\xampp\htdocs\ukvisa`, `http://localhost/ukvisa`), then migrate. MySQL DB = `ukvisadb` (root/no-pass). wp-cli = `C:\xampp\wp-cli.phar`.
2. **Run wp-cli via the Bash tool** (clean stdout). In PowerShell, never `2>&1` on native exes (wraps stderr as errors); never name a var `$pid` (read-only). In Git Bash, guard `%`/leading-`/` args from MSYS path conversion.
3. **UI automation = Playwright** (`automation/`, `HEADED=1` to show the window). Reuse `lib.mjs` (persisted login).
4. **Verify every step** with an explicit check (HTTP 200, schema valid, option value, render bytes). Don't claim done without the check.
5. **Git tracks only portable artifacts:** child-theme glue snippet, Pods config export, RankMath export, `automation/`, `docs/`, data JSON reference. WP core/plugins/DB are not committed.
6. **Brainstorm before custom; confirm before destructive** (deletes, overwrites, anything outward-facing).

## 3. Design rules (brand direction A — Trust)
- **Colours:** navy `#0A2540` (primary/headers), blue `#1456B8` (CTA/links), light `#EEF3FA` (section bg), **gold `#C8A24A` reserved for the Premium tier only**, text `#1B1B1B`, white `#FFFFFF`.
- **Type:** Inter — H 700/600, body 400. Buttons blue, 6px radius, 600.
- Apply via the **global Kit Styles** (Elementor Site Settings) so it cascades site-wide.
- **Reuse Travisa kit templates** (Home, Visa, Visa Detail, Pricing, FAQ, About, Contact, Blog, Header, Footer, 404). Don't rebuild chrome from scratch.
- Mobile-first; protect Core Web Vitals (LCP <2.5s, INP <200ms, CLS <0.1); Lighthouse mobile ≥90.
- Global **"Independent service — not a government website"** disclaimer in header strip + footer + order emails.

## 4. Content & SEO rules
- **Silo discipline:** support guides up-link to their money page (partial/branded anchor) + 1–2 siblings; **never cross-silo**; one contextual CTA each.
- **≤1 exact-match internal anchor** per page; rest partial/branded.
- **Answer-first** passages at the top of guides (AI-citable).
- **Schema by page type** (RankMath): money page = Service + FAQPage + HowTo + BreadcrumbList; guide = Article (+FAQ/HowTo); Org + WebSite site-wide.
- 8 launch destinations: Turkey (canonical) → Egypt, India, Morocco, UAE, Australia, USA, Schengen-hub.

## 5. Compliance rules (non-negotiable, from research)
- **Express speeds our handling + queue, NOT the government's decision.** Never promise faster approval.
- **ETA issues no document** (USA/Australia) — store **passport number** as the fulfilment key; eVisa = emailed PDF.
- **IDP = guided self-service.** UK IDPs are **in-person-only at PayPoint** (not Post Office). Never imply online/postal issuance. Checker must take **licence type**: an IDP is **not** needed in EU/EEA/CH/NO/IS/LI for **photocard** holders (only paper-licence). IDP genuinely required only for non-EEA 1926/1949/1968 countries.
- **Refunds:** government fee never refundable; service fee sliding scale (100% pre-submission → 75% mid-review → 0% once filed); 24h cancel window.
- **GDPR/ICO:** encrypt uploaded documents, expiring links, auto-delete N days post-delivery; cookie consent gating GA4/Clarity.

## 6. Definition of done (per page/feature)
A feature is done only when: it renders server-side (SEO-safe where public), reads data from Pods (no duplication), passes its schema check, follows the brand tokens, links per silo rules, shows the disclaimer where required, and a verification command confirms HTTP 200 + expected content.
