# Content Silos — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #2 of 6)
**Depends on:** Foundation (#1). **Shared with:** Tools (#3), /apply funnel (#4) — all read the visa JSON defined here.

## Goal
The SEO engine: per-destination money pages + support-guide silos that rank for outbound UK visa queries and funnel visitors to `/apply`. Defines the **shared visa data model** (single JSON source of truth) consumed by money pages, the checker (#3), and the funnel (#4).

## Decisions (locked)
| Item | Choice |
|---|---|
| Data source | **Single versioned JSON dataset** — one file per destination (`data/visas/<slug>.json`). Source of truth for #2/#3/#4. |
| Render path | **Server-side WP shortcodes read JSON** at render → crawlable HTML. Prose hand-written around shortcodes. |
| Money pages first | 8 launch destinations gate launch; guides phased after. |
| Link discipline | Guides up-link to money page (partial/branded anchor) + 1–2 siblings; **never cross-silo**; 1 CTA each. |
| Schema | Money page: Service + FAQPage + HowTo + BreadcrumbList. Guide: Article + (FAQPage/HowTo where fits). |

## Shared visa data model (source of truth)
One file per destination, `data/visas/<slug>.json`:

```jsonc
{
  "slug": "turkey",
  "name": "Turkey",
  "region": "europe-asia",
  "flag": "🇹🇷",
  "visa": {
    "required_for_uk": true,
    "type": "eVisa",                 // eVisa | eTA | visa-on-arrival | visa-free | embassy
    "evisa_available": true,
    "max_stay_days": 90,
    "validity_days": 180,
    "entry": "single",               // single | multiple
    "govt_fee_gbp": 0,               // official fee, passed at cost
    "processing": { "standard_days": 3, "express_hours": 24 },
    "requirements": ["Passport valid 150+ days from entry", "Return/onward ticket", "Accommodation details"],
    "how_to_steps": ["Check passport validity", "Complete application", "Pay & receive eVisa by email"],
    "notes": "Passport must have a blank page."
  },
  "idp": {
    "recommended": true,
    "permit_type": "1949",           // 1949 | 1968 | both | not-needed
    "notes": "Turkey recognises the 1949 permit for short visits."
  },
  "tiers": { "standard_gbp": 29, "express_gbp": 49, "premium_gbp": 79 },
  "updated": "2026-06-11"
}
```

- **Consumers:** checker (#3) reads `visa.*`; money page shortcodes render `requirements`/`how_to_steps`/`processing`/fees; funnel (#4) prices from `tiers` + `visa.govt_fee_gbp`; IDP cross-sell reads `idp.*`.
- **Storage:** JSON files live in the child theme (`wp-content/themes/hello-child/data/visas/`) and are versioned in git under `wordpress/hello-child/data/visas/`. One edit propagates to all consumers.
- **Validation:** a JSON schema (`data/visa.schema.json`) validates every destination file in CI/pre-commit (required keys, enum values, numeric fees).

## Money page shortcodes
Custom shortcodes (registered in `hello-child/functions.php` via an includes file), each reads `data/visas/<dest>.json`:

| Shortcode | Renders | JSON source |
|---|---|---|
| `[visa_status dest=turkey]` | status badge ("eVisa required — 90 days") | `visa.required_for_uk`, `type`, `max_stay_days` |
| `[visa_requirements dest=turkey]` | bulleted checklist | `visa.requirements` |
| `[visa_howto dest=turkey]` | numbered steps + HowTo schema | `visa.how_to_steps` |
| `[visa_fees dest=turkey]` | price table: service tiers + govt fee at cost | `tiers`, `visa.govt_fee_gbp` |
| `[visa_processing dest=turkey]` | standard/express timeline | `visa.processing` |
| `[idp_crosssell dest=turkey]` | "Driving in Turkey? You need a 1949 IDP" card → `/idp/` | `idp.*` |
| `[apply_cta dest=turkey product=visa]` | primary CTA → `/apply?dest=turkey&product=visa` | — |

Missing/invalid `dest` → shortcode renders nothing + logs a notice (never a fatal error or visible placeholder).

## Money page composition (matches parent §13.2 wireframe)
Block order: H1 + `[visa_status]` → intro prose → `[visa_requirements]` → `[visa_fees]` → `[apply_cta]` → `[visa_howto]` → `[visa_processing]` → `[idp_crosssell]` → FAQ (FAQPage schema) → guide links (silo up-links). **One exact-match anchor max** per page; rest partial/branded (parent §3.2).

## Support guides
- H1 = informational query · 800–1500 words hand-written · answer-first passage at top (AI-citable).
- Schema: Article + (FAQPage/HowTo where the format fits).
- Links: 1 up-link to money page (partial/branded anchor) + 1–2 sibling guides; **never cross-silo**; 1 contextual CTA to `/apply` or the checker.
- No shortcode data blocks (prose only; structured data stays on the money page).

## Silos at launch
8 money pages: **Turkey, Egypt, India, Morocco, UAE, Australia, USA, Schengen-hub**. Turkey = canonical template (parent §3.1) replicated per destination. Schengen = hub + country sub-pages (parent §3.3). IDP silo owned by #6.

Guide phasing after launch: highest search-volume guide first per silo (e.g. Turkey: things-to-do 9,900 → weather-Nov 5,400 → best-time 1,600 → …), per parent §3.1 inventory.

## Content production workflow
Per page: outline → draft prose → insert shortcodes (money pages) → RankMath check (focus keyword, readability, schema valid) → wire internal links per discipline → publish. Money pages are the launch gate; guides follow.

## Out of scope
Tool widgets (#3) · funnel/payment (#4) · CRM (#5) · IDP pages (#6) · the `/apply` page itself (Foundation stubs it; #4 builds it).

## Acceptance criteria
- 8 money pages live, each rendering all 7 shortcodes from JSON with no placeholder/error output.
- `data/visas/*.json` validate against `visa.schema.json`; editing one file updates the rendered page.
- Each money page: Service + FAQPage + HowTo + BreadcrumbList schema valid in Rich Results Test; ≤1 exact-match internal anchor.
- Internal links follow silo discipline (guide → money up-link + siblings only, no cross-silo).
- At least one support guide per launched silo published with Article schema and correct up-link.

## Open items
- Accurate per-country data (requirements, official fees, 1949/1968 mapping) for all 8 — research + fill JSON.
- Final guide inventory + priority per non-Turkey silo (parent §3.1 covers Turkey).
