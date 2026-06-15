# Complete Website Build Plan — coded front-end → WordPress

**Date:** 2026-06-15
**Goal:** Take the coded design system (3 page templates done) to a complete, launchable UKVisaCo site — every page type built, ported into the live WordPress build, wired to real data, QA'd, launched.
**Method:** parallel subagents per wave (ukv-parallel-build), test/verify before commit, design-system-first so every page stays consistent.

---

## Where we are

**Done (coded, `frontend/`):**
- `assets/ukv.css` — shared design system (palette, type, components)
- `assets/ukv-illustrations.js` — SVG sprite (skylines, stamp, route, monogram) + motion
- `index.html` (home), `destination.html` (money/Turkey), `apply.html` (eligibility-aware intake)

**Live WordPress (`C:\xampp\htdocs\ukvisa`):** all back-end logic already exists as mu-plugins (orders hub, eligibility router, tracker, emails, tracker shortcodes, Pods `destination` CPT, Forminator checker/apply, Stripe, RankMath). The coded front-end is the **visual layer** that must wrap this.

**Decision driving the plan:** WordPress stays the platform (back-end is built and works). The coded design becomes a **WP theme** that renders the existing dynamic data. React SPA is *not* needed and would orphan the back-end.

---

## Page inventory (page TYPES, not every instance)

Templates — one per type; dynamic instances (8 destinations, N guides) render from data.

| # | Page type | Coded? | Source data when in WP |
|---|-----------|--------|------------------------|
| 1 | Home | ✅ | static + `[ukv_*]` shortcodes |
| 2 | Money / destination | ✅ (Turkey) | Pods `destination` CPT |
| 3 | Apply / intake | ✅ | Forminator #299 + eligibility router |
| 4 | Destinations index (hub) | ⬜ | Pods loop |
| 5 | Track / status | ⬜ | `ukv-tracker` shortcode |
| 6 | Tools / checker (visa + IDP) | ⬜ | Forminator #297 + IDP checker |
| 7 | IDP / driving-abroad | ⬜ | static + IDP checker |
| 8 | Contact | ⬜ | callback Forminator + real numbers |
| 9 | About / trust | ⬜ | static |
| 10 | Legal (privacy/terms/complaints/disclaimer) | ⬜ | static, one shared template |
| 11 | Blog / stories index + single | ⬜ | WP posts (anonymised content engine) |
| 12 | SEO comparison ("X vs Y") | ⬜ | static template |
| 13 | SEO guide / hub | ⬜ | WP posts / static |
| 14 | Confirmation / thank-you (post-pay) | ⬜ | order ref via Space-Mono MRZ |
| 15 | 404 | ⬜ | static |

---

## Build waves (coded prototypes)

Each wave = parallel subagents, one file per agent, all consuming the shared assets, repo-only, parent verifies + commits. No agent redefines the palette.

**Wave A — conversion-critical (build first):**
- `destinations.html` (index/hub — boarding-pass card grid, filterable by type)
- `track.html` (status tracker — boarding-pass progress, order-ref MRZ lookup, stage timeline)
- `tools.html` (checker hub — visa checker + IDP checker, checker-led like the hero)
- `confirmation.html` (post-payment — order ref as `UKV<2026<NNNN<<<`, next steps, reassurance)

**Wave B — trust + content:**
- `idp.html` (driving-abroad silo — guided self-service framing; IDP = PayPoint, in-person)
- `contact.html` (real numbers, callback form, "calls are our main channel" emphasis)
- `about.html` (independent-service trust story, no government-affiliation, team/compliance)
- `legal.html` (one template → privacy / terms / complaints / disclaimer variants)

**Wave C — SEO surface:**
- `blog-index.html` + `blog-single.html` (stories/guides; anonymised, leak-safe)
- `compare.html` (X vs Y comparison template)
- `guide.html` (hub/pillar template)
- `404.html`

After each wave: open in browser, run **web-design-guidelines** + **ui-ux-pro-max** review pass, fix findings, responsive/a11y check, commit.

---

## WordPress port (the real deliverable)

Once page types are validated as coded prototypes:

**P1 — Theme scaffold**
- Create a child theme (or block theme) `ukvisa-doc`. Enqueue `ukv.css` + `ukv-illustrations.js`. Register the 6 colours + 3 fonts as theme.json / kit globals so Elementor and blocks inherit them.
- Port header/topbar/footer into theme `header.php`/`footer.php` (or template parts). MRZ strip = reusable block/part. Lock the disclaimer strip ("not a government website").

**P2 — Map templates to WP**
- Home → front-page template (static sections + existing `[ukv_*]` shortcodes for live data).
- Money page → single `destination` template bound to Pods fields (fee, validity, type, required docs, processing) — replaces hard-coded Turkey values with field output.
- Apply → page hosting Forminator #299; the eligibility branch (standard vs manual-review) wired to the pre-payment funnel split (task #118).
- Track → page with `ukv-tracker` shortcode styled to the boarding-pass design.
- Tools → checker #297 + IDP checker, styled checker-led.
- Destinations index → Pods archive loop emitting boarding-pass cards.
- Legal/About/Contact/IDP → static templates + callback Forminator.
- Blog → style WP loop + single to `blog-*` design.
- SEO compare/guide → page templates or block patterns.
- Confirmation → Stripe/Forminator success redirect → styled order-ref page.

**P3 — Dynamic data wiring (depends on existing pending tasks)**
- #96 real required-docs + passport-validity per destination → money page renders them.
- #95 real supply-chain nodes (internal, not public).
- #97 Forminator apply fields (expiry/travel/consent + eligibility axes).
- #98 live Stripe (real checkout + reconciliation), #99 AI vision doc review (optional).
- Destination skylines: generic SVG per type now; swap to per-destination art (nano-banana-edit or supplied photos) where wanted.

---

## QA + launch gates

1. **Responsive QA** — every template at 360 / 768 / 1280 (#63).
2. **Accessibility** — web-design-guidelines pass: labels, focus, contrast, reduced-motion, aria on the apply panels.
3. **Compliance copy audit** — every page: not-a-government-site, fee-separate, express≠decision, no approval guarantee, IDP=self-service.
4. **SEO** — RankMath titles/meta, schema (money pages + FAQ + comparison), sitemap, hreflang n/a.
5. **Content accuracy** — #129 verify fees + processing vs gov.uk before publishing money pages.
6. **Security** — ROTATE live HubSpot token; secrets out of repo; admin 2FA (#139).
7. **Hosting** — #17 migrate local → production host + domain; #66 launch activation checklist.

---

## Sequencing

1. **Now:** Wave A (4 pages, parallel) → review → commit.
2. Wave B (4 pages) → review → commit.
3. Wave C (4 pages) → review → commit.
4. WP port P1 (theme scaffold) → P2 (template mapping) → P3 (data wiring).
5. QA gates → host migration (#17) → launch (#66).

**Parallelism:** Waves A/B/C are ~12 independent files → subagent fan-out, ~4 per wave. WP port P2 templates are also largely independent → fan-out per template.

**Risks:** (a) coded↔WP divergence — mitigated by porting the *same* ukv.css into the theme, not re-styling; (b) Pods field gaps — money-page template must degrade gracefully when a field is empty; (c) data accuracy (#129) blocks publishing money pages, not building them.

---

## Task additions

- #147 Wave A coded pages (destinations index, track, tools, confirmation)
- #148 Wave B coded pages (idp, contact, about, legal)
- #149 Wave C coded pages (blog index/single, compare, guide, 404)
- #150 web-design-guidelines + ui-ux-pro-max review pass on all coded pages
- #151 WP theme scaffold (enqueue assets, globals, header/footer parts) — folds in #146
- #152 WP template mapping (home, money, apply, track, tools, index, legal, blog, compare, confirmation)
- #153 WP dynamic data wiring (ties #95–99, #118)
- #63 responsive QA (existing), #66 launch (existing), #17 host (existing)
