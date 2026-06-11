# Foundation — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #1 of 6)

## Goal
A live, branded, SEO-ready WordPress shell that every other subsystem (content silos, tools, `/apply` funnel, CRM, IDP) builds on. No product/conversion logic here — just the platform, brand, nav, legal, analytics, and performance baseline.

## Decisions (locked)
| Item | Choice |
|---|---|
| Hosting | **Managed WP — SiteGround GrowBig** (UK datacentre, staging, daily backups, caching, free SSL/CDN). Swap to Kinsta if budget allows. |
| Domain | **Register new** (open item — brandable `.com`/`.co.uk`; UK-trust name). |
| Environment | Staging subdomain → production; SSL forced; WWW/non-WWW canonical decided + redirected. |
| Base theme | **Hello Elementor** (lightweight) as base for the Travisa kit. |
| Page builder/kit | Elementor + Jeg Elementor Kit + MetForm (Travisa requirements) + import **Travisa** kit; keep only needed templates. |
| SEO | **RankMath** (titles, schema base, sitemap, redirects). |
| Brand (direction A) | Navy `#0a2540`, blue `#1456b8`, light `#eef3fa`, white; **gold `#c8a24a` reserved for Premium tier**; clean sans (Inter/system); rounded CTAs; wordmark logo. |

## Scope (what this sub-project delivers)
1. **Environment**: SiteGround set up, domain registered + pointed, staging + production, SSL, backups, caching (host + WP Rocket if needed), security (Wordfence/host WAF), GDPR cookie consent.
2. **WordPress base**: Hello Elementor + Elementor + Jeg Kit + MetForm; import Travisa; remove demo bloat; set brand tokens (global colours/fonts/buttons) to direction A.
3. **Global chrome**:
   - Header nav: `Destinations ▾` · `IDP` · `Tools ▾` · `How it works` · `Pricing` · primary CTA.
   - Footer = full silo index (Destinations · Products · Tools · Company) + legal links + "Independent service — not a government website" disclaimer.
4. **Legal / trust pages** (content + routes): `/how-it-works`, `/pricing` (skeleton — populated in Pricing/Content work), `/refunds`, `/terms`, `/privacy`, `/about`. Global disclaimer in header strip + footer.
5. **SEO base**: RankMath configured (title templates, Org + WebSite schema, breadcrumbs), XML sitemap generated + submitted to GSC; robots.txt; `/apply` set `noindex` placeholder.
6. **Analytics**: GA4 install, GSC verify, Microsoft Clarity, Looker Studio data sources connected (GA4 + GSC).
7. **Performance baseline**: Core Web Vitals targets — LCP < 2.5s, INP < 200ms, CLS < 0.1; image optimisation, caching, minimal plugin footprint.

## Out of scope (other sub-projects)
Destination money pages + silos (#2) · tools (#3) · `/apply` funnel + Stripe (#4) · Zapier/Pipedrive CRM (#5) · IDP pages/flow (#6). Foundation only stubs their routes/nav entries.

## Acceptance criteria
- Branded homepage shell + nav + footer live on production over HTTPS.
- All 6 legal/trust routes resolve with disclaimer present.
- RankMath active; sitemap submitted; Org + WebSite schema validates in Rich Results Test.
- GA4 + GSC + Clarity recording; Looker dashboards connected.
- Lighthouse mobile ≥ 90 performance on the homepage shell.

## Open items
- Domain name choice (brand).
- SiteGround account + Envato Elements licence for Travisa.
- Final logo/wordmark asset.
