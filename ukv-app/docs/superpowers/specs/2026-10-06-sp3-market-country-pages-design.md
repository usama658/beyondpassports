# Sub-project 3: market x destination Schengen pages on beyondpassports.com (design spec)

**Status:** draft for owner review. Written 2026-10-06. Sub-project 3 of 7 in the international + tours programme.
**Decision basis:** `docs/product-goals-2026-10.md` (G3 primary; G1, G2, G5 served) and `docs/superpowers/research/2026-10-06-README-decision-basis.md` (14 reports). Every numbered decision names the competitor finding it rests on (locked rule: every decision is competition-led).
**Builds on:** `2026-10-06-international-market-foundation-design.md` and its plan (Market value object, `market_url()`, `ResolveMarket`, the `/{market}` route group, `MarketHubController::SCHENGEN`, `partials.market-trust-strip`, `partials.market-price`, `partials.hreflang`, `MarketAlternates`, `SitemapIntlController`). Nothing here touches `.co.uk` content except one head injection for hreflang reciprocity (section 8.3).
**Owner decision already taken:** new Blade templates driven by data, not clones of the static france-gold file.

## How to use this document
1. Read sections 1 to 3. If the goal, the scope or an assumption is wrong, say so first.
2. For each decision in sections 4 to 12 ask: does it move a G3 scoreboard line, and does the cited finding support it? Strike or amend anything that fails.
3. Approve section by section. "Approved" on the whole document unlocks the implementation plan (`2026-10-06-sp3-market-country-pages.md`). Nothing is built until the plan is approved too.
4. Section 14 is the acceptance checklist after build.
5. Change this file first, then the plan, then the code.

## 1. Goal
Ship a data-driven page type at `beyondpassports.com/{market}/schengen-visa/{country}` for 4 markets x 29 destinations (116 URLs), where every page is assembled from one verified `market_destination_profiles` row and renders: a market-specific H1, server-rendered government and visa-centre fee table, honest per-operator appointment copy, a documents table with a "common mistake" column, 10 to 15 FAQs in FAQPage schema, the full trust and schema set, reciprocal hreflang across the five siblings, a Markdown twin and an entry in a generated `/llms.txt`.

Scoreboard lines served: G3 (116 pages indexed; structural SEO edge: hreflang on destination pages, server-rendered fee tables, llms.txt + .md twins, named reviewer), G2 (price block via `partials.market-price`, govt and VAC fees shown separately), G1 (honest appointment copy per operator, "we will check for you" block), G5 (trust strip + Organization identifiers on every page).

Success: Canada x Italy and South Africa x Spain render fully from seeded, verified rows; every other cell renders the honest fallback until its row is verified; no page ever prints a fee, centre, operator or rule that lacks a `verified_at` date and a source URL.

## 2. Scope
In: the `market_destination_profiles` table, model, model-level verification guard, copy-rules guard, Filament resource, idempotent seeder with the first two verified profiles, the country template and the fallback template, the JSON-LD set, `MarketAlternates::forDestination()`, UK reciprocity injection, `sitemap-intl` extension, hub links, `.md` twin, `/llms.txt`, market-level qualification tiles, tests, runbook notes.
Out: per-market live availability snapshots (SP4 supplies them through the `MarketAvailability` seam defined in 6.10), local-currency prices and checkout (SP5; prices stay null), a market-aware lead modal (SP5; see assumption B2), compliance memos and VAC accounts (SP6), DNS, Search Console, ads, Quebec French mirror (SP7), the trips block on the country page (SP2 owns the catalogue; the page reserves a slot, section 6.13 and the amendment in section 17), UK `.co.uk` Portugal/Greece pages (the template is market-agnostic and can serve `Market::uk()` later, not in this spec), AggregateRating schema (no real per-market reviews exist; never fabricated).

## 3. Assumptions standing in for open owner decisions
- B1 The foundation plan is merged before SP3 Task 1 starts. SP3 reuses its classes and tests. If the foundation changes a signature, update SP3's plan before running it.
- B2 The shared modal flow (`partials.lp-flow`) is NOT included on `.com` country pages. Reason: it hard-codes the UK tiers (£49 / £89 / £158, lines 215-217) and the "How much time is left on your UK e-visa?" step (line 251). A market-aware flow waits for SP5 prices. CTAs on `.com` are WhatsApp deep links with prefilled text carrying destination, market label and the `[CODE]` tag (foundation section 8).
- B3 Prices remain null in every market. The fee table shows government and visa-centre rows only; `partials.market-price` renders nothing until the owner sets a price (foundation 4.1; document-lane deep dive 6.2 point 1).
- B4 The named reviewer for the first two profiles is the person who ran the 2026-10-06 verification pass (owner). `reviewed_by` is free text so a second ops identity can take over later (foundation A3).
- B5 Word depth: the template plus a complete profile should land in the 3,000 to 4,500 word band (SEO footprint synthesis c). The seeded profiles are complete but the band is an editorial gate enforced by the admin "Words" column and the launch checklist (section 12), not by a hard database rule, so a verified row can be saved while copy is still being extended.

## 4. The `market_destination_profiles` dataset
### 4.1 Storage choice: database table + Filament resource + idempotent seeder (not a JSON/YAML file)
Chosen: one table, one Eloquent model with scopes and a saving guard, one Filament resource under a new "International" navigation group, and `database/seeders/MarketDestinationProfileSeeder.php` (updateOrCreate per market x slug) registered in `ProductionSeeder`.
Why a table and not a versioned JSON/YAML seed:
- The two fields that change most, `verified_at`/`reviewed_at` and the `verified` flag, change on an ops cadence, not a deploy cadence. The SA deep dive's risk 4 is explicit: "static VAC map rots in months" (new TLS Pretoria centre, Capago fee changes, Switzerland moved to VFS). A table lets ops re-verify a row and bump the date without a release. Atlys and Visard both expose freshness ("Updated Sep 30, 2026"; "How We Reviewed This Page"); a date that only moves on deploy would be stale by construction.
- The existing `Destination` model already carries `facts_checked_at`, `review_interval_days` and `sources` with a Filament form (`DestinationResource`, "Data freshness & sources" section). The pattern exists; SP3 follows it.
- Sitemap, hreflang, hub links and `llms.txt` all need "verified AND published" filtering per market. A query is the natural tool; a file would need a loader and a cache.
- The seeder gives git history for the first verified rows (the version-control benefit of the JSON option) and remains the canonical import path for later batches: ops write a batch, a developer commits it as seed data, and the seeder's updateOrCreate keeps production in sync.
Rejected: pure JSON/YAML in `config/` (every verification pass becomes a deploy; no per-row reviewer audit), and the CMS `pages` table (block builder is for prose pages, not a typed matrix).

### 4.2 Schema (migration `create_market_destination_profiles_table`)
| Column | Type | Meaning |
|---|---|---|
| market | string(2) | `za` `ae` `us` `ca` (must exist in `config('ukv.markets')`) |
| destination_slug / destination_name / destination_iso | string | from `SchengenDestinations` (section 5.1); unique with market |
| operator | string enum | `vfs` `tlscontact` `bls` `capago` `gvcw` `embassy_direct` `email_queue` |
| portal_name | string nullable | display name of the booking system ("Prenot@mi", "BLS Spain Visa portal", "RK-Termin") |
| booking_mode | string enum | `calendar` `waitlist` `allocation` `embassy_direct` `email_queue` |
| centres | json | list of `{city, name, serves}` |
| jurisdiction_note | text | which post serves which province/state/emirate; consequences of the wrong one |
| status_rule | text | residence-status rule in the consulate's own words, with the "3 months beyond" style thresholds |
| processing_text | string | "Usually within 15 days once lodged; up to 45 in busy periods" |
| appointment_copy | text | honest per-operator copy (section 6.5) |
| intro | text | the two-paragraph market-specific opening |
| govt_fee_eur_adult / govt_fee_eur_child | smallint | defaults 90 / 45; under 6 is always free and rendered as text |
| govt_fee_local_adult / govt_fee_local_currency / govt_fee_verified_at / govt_fee_source_url | decimal / string(3) / date / string | local figure only when verified; null renders EUR only |
| vac_fee_amount / vac_fee_currency / vac_fee_note / vac_fee_verified_at / vac_fee_source_url | decimal / string(3) / string / date / string | 0 with a note for embassy-direct posts |
| documents | json | list of `{document, detail, common_mistake}` |
| faqs | json | list of `{q, a}` (10 to 15) |
| source_urls | json | list of `{label, url}` printed in the "How we reviewed this page" block |
| reviewed_by / reviewed_at | string / timestamp | named reviewer and date; feed `dateModified` |
| verified | boolean | facts verified against the sources above |
| published | boolean | owner switch; a verified row can still be held back |
Unique index on (`market`, `destination_slug`). The model is `App\Models\MarketDestinationProfile`; `scopeLive()` = verified AND published; `isLive()`.
Cited: SEO footprint (c) lists exactly these sections as the content benchmark; Visard's resources table is the only competitor with a "common mistake" column; VisaHQ's fee cells are JS-empty (e), so every figure here is a real column, server-rendered.

### 4.3 Enums and their copy
Operator labels: VFS Global, TLScontact, BLS International, Capago, Global Visa Center World, the consulate directly, the consulate by email. Booking modes drive step 4 of the process (section 6.6) and the availability block wording. The lists match the appointment-lane board design (section 7 of that deep dive: Calendar / Waitlist / Allocation queue / Embassy direct / Email queue) so SP4's snapshots slot in without a new vocabulary.

### 4.4 Verification gate (model level, not UI level)
`MarketDestinationProfile::saving` throws `LogicException` when `verified` is true and any of `reviewed_by`, `reviewed_at`, `govt_fee_verified_at`, `vac_fee_verified_at`, `vac_fee_source_url` is empty. The Filament form mirrors the rule as a validation message. The page controller renders the fallback (section 6.14) unless `isLive()`. Sitemap, hreflang, hub links, `.md` and `llms.txt` all use `scopeLive()`. Result: an unverified cell can never leak a number. Cited: country-logic.md governance ("`verify` fields never drive a public claim until sourced"); document-lane risk 6 ("copy-paste template errors ... add a price-consistency assert").

### 4.5 Copy rules guard
`App\Support\CopyRules::violations(string): array` flags an em dash, and these patterns case-insensitively: `guarantee`, `guaranteed`, `fast-track`, `fast track`, `priority appointment`, `early appointment`, `\bvip\b`, `30 minutes`, `30-minute`, `for indians`, `get visa`, `on time delivery`. The model guard runs it over every text field (including JSON) before save; tests run it over every rendered page. Cited: product goals "Excluded on purpose"; Atlys "for Indians" H1 and "Guaranteed On-Time"; EuroPath "Best prices guaranteed"; competitor-swot "Patterns BP must NOT copy"; foundation A3 (no 30-minute promise on `.com`); memory no-em-dash.

### 4.6 Seed: the first two verified profiles
`MarketDestinationProfileSeeder` carries Canada x Italy (consulate direct through Prenot@mi) and South Africa x Spain (BLS International) with the data in the US+CA and SA deep dives: operator, centres, jurisdiction split, status rule, fees (CAD 146.00 government, no centre fee; R1,775 + R333 BLS), the Toronto "it is a scam" quote and the BLS "no intermediaries" line, 10 document rows with mistakes, 10 FAQs, source URLs, reviewer and date, `verified = true`, `published = true`. They stay invisible until `UKV_MARKET_CA_ENABLED` / `UKV_MARKET_ZA_ENABLED` are flipped (foundation gates). Greece in any market is never seeded until the manual check listed in the service brief section 12 is done.

### 4.7 Market-level qualification tiles (not per destination)
`App\Support\MarketQualification::tiles(Market): array` holds the status tiles from the service brief section 4 (ZA 4, AE 4, US 8, CA 8) as a typed array with verdict `green|amber|red`. They are market copy, not destination data, so they live in code. The destination-specific override (for example Italy Vancouver accepting a visitor record of six months or more) belongs in the profile's `status_rule`.

## 5. Routes
### 5.1 Destination registry
`App\Support\SchengenDestinations` holds the 29 names, ISO codes and slugs (`Str::slug` of the name: `czechia`, `netherlands`, ...). `MarketHubController::SCHENGEN` becomes an alias of its `NAMES` constant so the foundation test (29 entries) keeps passing. The reserved slug list for the country routes is `SchengenDestinations::routePattern()`.

### 5.2 Routes inside the foundation's `/{market}` group
```
GET /{market}/schengen-visa/{country}.md   market.country.md   (text/markdown twin; 404 unless live)
GET /{market}/schengen-visa/{country}      market.country      (page or fallback)
```
Both constrained with `->where('country', SchengenDestinations::routePattern())`. The `.md` route is registered first. Market resolution, 404 for disabled markets and the noindex header for staged markets are inherited from `ResolveMarket`.

### 5.3 Site-wide
```
GET /llms.txt   llms.txt   (generated; host-agnostic; absolute URLs for both hosts)
```

## 6. Template section stack (`resources/views/market/country.blade.php`)
Standalone Blade page in the foundation's pattern (own head, `partials.lp-chrome`, `partials.lp-footer`, `partials.analytics-head`, `partials.utm-capture`), `lang` = market locale, canonical via `market_url()`. Order and source per section:
1. **Hero.** H1 exactly "Schengen visa for {destination} from {market label}". Sub-line: operator + portal + booking mode in one sentence from the profile ("Applications go to the Italian consulate for your province through Prenot@mi; appointments are free and personal"). Two CTAs: "Check {destination} availability" (WhatsApp, prefilled with destination + market + `[CODE]`) and "Start my file". Cited: competitor-swot approved stack item 1; Atlys subhead discipline ("zero adjectives"); Flypass mechanism line.
2. **Trust chips.** Companies House 17331903 (linked to the register), ICO ZC197159, "Appointments are free. We never sell one.", support hours from `Market::supportHours()`. No 30-minute chip on `.com`. Cited: approved stack item 2; service brief 7; Italy Toronto quote (US+CA deep dive).
3. **Who this page is for.** The US/CA ETIAS split sentence when the market is `us` or `ca` ("US or Canadian passport: ETIAS, EUR 20, not a visa, not live until late 2026. Other passport living here: this page."), then the market qualification tiles (4.7) and the profile's `status_rule`. Cited: service brief 4; US+CA deep dive section 3 (ETIAS/EES).
4. **Fee table (server-rendered) + `partials.market-price`.** Rows: government fee adult EUR 90 (local approx when verified, with "verified {date}"), child 6 to 11 EUR 45, under 6 free, visa-centre fee (amount + currency + note + "verified {date}" + source link, or the note alone for consulate-direct posts). Below it `partials.market-price` (renders nothing while null) and the fixed line "You can apply without us on the official portal for the government and centre fees alone. Our fee is for preparation and review only." Cited: approved stack item 3; VisaHQ fee cells JS-empty (SEO footprint 5); Aviva parenthetical; GoVisa/iVisa "apply directly" line (G2 steal). UAE wording of any future price line: see section 17.
5. **Appointments: how {operator} works here.** `appointment_copy` verbatim, the consulate's own anti-agent quote where one exists, and the booking-mode-specific step text (4.3). Cited: appointment-lane section 3 (operator lines verbatim), service brief 2 per-operator honesty lines, VFS "There is no fast-track service" (quoted in the deep dive, never in our copy).
6. **Process, six steps, we/you asymmetry, ending on the outcome.** 1 You tell us destination, dates, city and status (WhatsApp). 2 We route you to the right post and build your checklist. 3 We prepare the form, cover letter and itinerary against your real bookings. 4 Booking step text by `booking_mode`. 5 You attend {centre} with the appointment-day pack. 6 The consulate decides, usually within {processing_text}; you travel. Cited: approved stack item 4; EuroPath arc (swot); "the appointment is the bottleneck, not the decision" (US+CA deep dive).
7. **Documents table.** Columns: Document, What the consulate wants, Common mistake. Rows from `documents`. Cited: Visard resources table (SEO footprint 3), Aviva per-type depth (swot), market pain docs (document-lane 5).
8. **Deliverables preview.** The nine-item pack from the document-lane deep dive 6.4, labelled illustrative. Cited: Atlys "What you get" carousel; approved stack item 6.
9. **What we do and what we don't.** The appointment-lane section 7 draft, adapted: we do not sell, hold or block-book appointments; appointments are free and booked only on the official {operator} site in your name; no bots on your account, no credential sharing; we cannot create availability, promise a date or speed up the decision; not affiliated with VFS Global, TLScontact, BLS, Capago, any consulate or government; what we do. Cited: xVisa honesty section (swot); VisaD "What we don't do" (G1 steal); BLS and TLS intermediary warnings.
10. **Availability block.** "Appointment availability for {destination} from {market}: we will check for you" with the honest paragraph from the foundation hub, plus a `MarketAvailability::for(Market, slug)` seam that returns null now and a snapshot array in SP4 (earliest date seen, last checked, checked by). Cited: appointment-lane section 4 (Visa Catcher "last seen" honesty), foundation A2.
11. **Refusal recovery.** Five-year record line, Annex VI common grounds, "Review my refusal" CTA, the refund rule sentence (fee buys work not outcome; free redo if the refusal cites our error). Cited: TourLoom refusal funnel (swot steal 3); document-lane 6.3 refund text; Reg 2019/1155 grounds (US+CA deep dive 3).
12. **FAQ with FAQPage schema.** `faqs` rendered as `<details>` and emitted as JSON-LD. Cited: iVisa FAQPage (schema floor), Atlys persona FAQs, market FAQ lists in the deep dives.
13. **How we reviewed this page.** "Reviewed by {reviewed_by} on {date}. Sources:" list of `source_urls`. A reserved slot comment for the SP2 trips block sits directly below it (wording rules in section 17). Cited: Atlys "How We Reviewed This Page", Visard visible "Updated" stamps, gap analysis "nobody has a named reviewer per page".
14. **Siblings.** Other live destinations in this market (hub-and-spoke) and the same destination in other live markets plus the UK gold page where it exists. Cited: Visard hub-linked pages ranking with 303 URLs (SEO footprint a).
15. **Trust strip + disclaimer strip.** `partials.market-trust-strip` (which already wraps the locked `disclaimer-strip` partial). Cited: foundation 5; memory disclaimer-strip-partial.

### 6.14 Fallback template (`market/country-fallback.blade.php`)
Rendered for any cell that is not live: same chrome, same H1, "We will check for you" block, WhatsApp CTA, trust strip, link to the hub, `<meta name="robots" content="noindex, nofollow">` and `X-Robots-Tag: noindex, nofollow`, HTTP 200. No operator, centre, fee or rule text. Not in sitemap, hreflang, hub links or `llms.txt`. Cited: G3 avoid-list ("iVisa visa-free filler pages", "VisaHQ 89k thin"), country-logic `verify` rule.

## 7. Schema set (`partials.market-country-schema`)
One `@graph`:
- `Organization` with `@id {intl_base_url}#org`, `identifier` as two `PropertyValue`s (Companies House 17331903, ICO ZC197159), `url`, `areaServed`. Cited: Visard puts its company number in schema; nobody publishes ICO (gap analysis A13).
- `Service` (`serviceType` "Schengen visa document preparation and appointment guidance", `provider` Organization, `areaServed` Country = market, `audience` Audience). `offers` (`Offer` with `price`, `priceCurrency`) only when `Market::priceTotal()` is non-null. Cited: iVisa Product/Offer floor, Visard Service + Country + Audience.
- `BreadcrumbList`: market home, market hub, destination.
- `FAQPage` from `faqs`.
- `WebPage` with `dateModified` = `reviewed_at` ISO-8601, `reviewedBy` Person = `reviewed_by`, `inLanguage` = locale.
- No `AggregateRating` until real per-market reviews exist (G5 target: review collection within 30 days of first client). Fabricated ratings are on the avoid list.

## 8. hreflang
### 8.1 `MarketAlternates::forDestination(string $slug): array`
`en-GB` only when the UK gold page exists for that slug (`MarketAlternates::UK_COUNTRY_SLUGS`, mirrors the route whitelist: france, spain, netherlands, germany, italy, switzerland, belgium); each enabled and indexable market's locale only when its profile is live; `x-default` to the `.com` chooser (foundation 4.4). Returns `[]` when no non-UK alternate exists, so a lone UK page emits nothing.
### 8.2 Partial
`partials.hreflang` accepts an optional `alternates` array and falls back to `MarketAlternates::for($path)`.
### 8.3 Reciprocity with the static UK gold pages
`LpAssembler::inject()` gains a third argument `string $headExtra = ''` injected after the analytics head; the `/schengen-visa/{country}` route passes the rendered hreflang partial for `forDestination($country)`. Without this the cluster is one-directional and ignored. Cited: SEO footprint (d) "full reciprocal hreflang ... every page listing all 5 siblings"; gap analysis A8 "nobody does it on destination pages".

## 9. Sitemap
`SitemapIntlController` appends, for each enabled and indexable market, one `<url>` per live profile with `lastmod` = `reviewed_at` (never today's date for these rows) and priority 0.8. The UK `/sitemap.xml` is untouched. Cited: SEO footprint (d) "real per-URL lastmod"; Atlys's single build timestamp is "noise".

## 10. Markdown twin and llms.txt
- `/{market}/schengen-visa/{country}.md`: `text/markdown; charset=UTF-8`, generated by `App\Support\MarketCountryMarkdown::render()` from the same profile: title, intro, who it is for, fees as a Markdown table, appointment section, process, documents table, what we do/don't, FAQs, reviewed-by and sources. Headers: `Link: <html url>; rel="canonical"` and `X-Robots-Tag: noindex` (the HTML is the indexable copy). 404 unless live. Cited: Atlys `/docs/{dest}.md` twins (SEO footprint 1, e).
- `/llms.txt`: `text/plain`, sections: site summary with identifiers; United Kingdom (hub + gold pages); one section per enabled and indexable market listing live pages with operator, booking mode, reviewed date and the `.md` link; "Facts a machine might need" (EUR 90/45/free, appointments free on official sites, no one can buy an earlier date or shorten processing, client attends in person). Cited: Atlys ~800-line sectioned llms.txt; Visard "Facts a machine might need". `robots.txt` already allows the AI crawlers (foundation Task 10).

## 11. Copy rules (page and data)
No em dashes. Nothing from CopyRules (4.5). No dated appointment promises, no success-rate percentages unless sourced with year (country-logic governance), no "Get a quote" placeholders, no "for Indians" style template leaks, no "package" wording, no dummy-booking offers, no WhatsApp-call offer in UAE copy (foundation A5), `support_hours` from config rather than any reply-time promise. Operators' own anti-agent quotes are printed as quotes with attribution. Every figure prints its `verified_at` date next to it.

## 12. Build order and gates
Order (product goals, competitor targeting map): Phase A Canada x 29 and South Africa x 29 (CA has 19 of 29 destinations with no page from anyone; ZA has none from any global). Phase B UAE and US long tail (big five first for AE against Atlys and locals, then the long tail). Phase C UK Italy/Spain/Portugal/Greece as `.co.uk` additions using the same template with `Market::uk()` (separate spec; Atlys-blank cells).
Per-cell gate before `verified = true`: official source URL for operator, centres and fees; fee figures dated within 90 days; status rule quoted from the consulate; Greece never before the manual browser pass (service brief 12). Per-cell gate before `published = true`: ten or more FAQs; ten or more document rows; appointment copy written for the real booking mode; "Words" column at 3,000 or more; CopyRules clean (automatic). Per-market gate before `indexable = true`: foundation gates (one logged VAC check per core destination, compliance memo, one live local charge) plus at least five live profiles so the hub is not a single link.
Seeded now: CA x Italy, ZA x Spain.

## 13. Error handling
Unknown slug: 404. Known slug, no row or not live: fallback 200 + noindex. Live row in a non-indexable market: full page + noindex header, excluded from sitemap, hreflang and llms.txt. `.md` for a non-live row: 404. Missing local government fee: EUR row only. Missing `portal_name`: operator label used. Empty `faqs`: FAQ section and FAQPage omitted (cannot happen for a published row under the gate, but the template is null-safe). Model guard failures surface as Filament validation messages and as `LogicException` in seeders and tests.

## 14. Testing (acceptance checklist), `php artisan test --filter=MarketCountry`
1. Live profile renders 200 with the exact H1 and the operator label; unknown slug 404; disabled market 404.
2. Non-live profile renders the fallback: H1 present, "check for you" present, noindex header and meta, no fee figure, no operator word.
3. Fee table shows EUR 90 / EUR 45 / free, the local government figure with its verified date, the VAC figure with currency, date and source link; `partials.market-price` renders nothing while null and the total once set.
4. Documents table has the "Common mistake" header and every seeded row; FAQ count equals `count(faqs)`.
5. JSON-LD: Organization identifiers 17331903 and ZC197159; Service present; Offer absent with null price and present with a price; BreadcrumbList with three items; FAQPage question count; WebPage dateModified equals `reviewed_at`.
6. `forDestination('italy')` includes en-GB and en-CA when CA is indexable and the profile is live; excludes en-CA when the profile is unverified; `forDestination('austria')` has no en-GB; UK `/schengen-visa/italy` head lists en-CA (reciprocity).
7. `sitemap-intl.xml` lists the live URL with `lastmod` = reviewed date and omits unverified rows and non-indexable markets.
8. `.md` twin: 200, `text/markdown`, canonical Link header, contains the H1 and "Reviewed by"; 404 when not live.
9. `/llms.txt`: lists live pages and `.md` links, omits unverified rows, contains the facts section.
10. Hub links live destinations and lists others as plain text.
11. CopyRules: every rendered page (live, fallback, `.md`, llms.txt) has no em dash and no banned pattern; model refuses to save content with a violation.
12. Model guard: `verified = true` without reviewer, dates or source URL throws; with them saves.
13. Seeder is idempotent (two runs, two rows) and both seeded pages exceed 2,200 words of main content.
14. No `SlotBoard`/`SlotTiles` markers and no `id=mov` (lp-flow) on any `.com` country page.
15. Admin smoke test boots the new resource index and create pages.
Existing suites stay green: `MarketHubPageTest`, `MarketSeoTest`, `MarketSitemapTest`, `LpAssemblerTest`, `AvailabilityServiceTest`.

## 15. Competitor citation per decision (the locked rule, in one table)
| Decision | Beats / learns from | Source |
|---|---|---|
| Table + Filament + seeder, dated per row | Atlys single build timestamp is noise; Visard visible "Updated" stamps; "static VAC map rots" | SEO footprint 1, 3; SA deep dive risk 4 |
| `verified` gate, fallback never invents | VisaHQ 89k thin pages; iVisa filler; country-logic `verify` rule | G3 avoid list; country-logic.md |
| H1 "Schengen visa for {dest} from {market}" | Atlys "France Visa for Indians" leaks; VisaHQ geo-templated title | SEO footprint 1, 5; swot Atlys W |
| Server-rendered fee table with dates | VisaHQ cells JS-empty; Atlys/iVisa no tables | SEO footprint 5, (e) |
| Govt and VAC fees separate, never collected | Aviva £385 bundle; iVisa all-inclusive; Visa Agent pays fees | document-lane 4.5, 6.6 |
| "Apply directly" line beside fees | GoVisa/iVisa line; everyone else hides it | G2 steal; document-lane 6.2 point 5 |
| Honest per-operator appointment copy with the consulate's quote | Italy Toronto "it is a scam"; BLS "fraudulent practice"; VFS "no fast-track"; Musafir/Odit "priority" words | US+CA 2, 4; appointment lane 3; UAE 6 |
| Booking-mode enum drives step 4 and availability | TLS France UK allocation makes "refresh faster" wrong advice; Spain Toronto email queue | appointment lane 6.5; US+CA corrections |
| Documents table with "common mistake" | Visard resources table; Breakout "one missing signature" | SEO footprint 3; swot Breakout |
| Deliverables preview labelled illustrative | Atlys carousel; flows-serve-not-inform | swot steal 5; document-lane 6.4 |
| "What we do and what we don't" | xVisa honesty section; VisaD "What we don't do" | swot steal 6; appointment lane 7 |
| Refusal recovery with own CTA | TourLoom funnel; BP five-year line | swot steal 3; lead-refusal playbook |
| FAQPage + Organization identifiers + Service/Audience + dateModified | iVisa schema floor; Visard additions; nobody emits ICO | SEO footprint (b); gap analysis A13 |
| Offer only when price set; no AggregateRating | fake counters on GoVisa/xVisa/TourLoom; fabricated stats UAE/ZA | swot headline; document-lane 4.3 |
| hreflang on destination pages incl. UK reciprocity | Visard omits on destination pages; Atlys sitemap-only, no x-default | SEO footprint 3, (d); gap A8 |
| `.md` twins + sectioned llms.txt | Atlys the only comparable asset; iVisa thin; GoVisa broken | SEO footprint 1, 2, 4, (e) |
| Named reviewer + sources block | Atlys has no named author; gap "named reviewer per page" | SEO footprint 1; G3 gap |
| No lp-flow on `.com` | its hard-coded UK tiers and UK e-visa step would be invented prices abroad | lp-flow.blade.php 215-217, 251; foundation 4.1 |
| CA + ZA first | CA 19/29 blank, ZA blank from every global; Atlys adding locales | competitor targeting map; market gap map |

## 16. Open items carried forward
SP4: snapshot ingestion feeding `MarketAvailability::for()`. SP5: prices per market, market-aware lead flow, Offer schema goes live automatically. SP2: trips block in the reserved slot (section 17 rules). SP6: operator verification passes for Greece (all markets), TLS France US, VFS fees NL/CH/PT/AT, France operator in Dubai. SP7: Quebec French mirror before Quebec ads; Search Console for `.com`. Later spec: UK Portugal/Greece on the same template with `Market::uk()`.

## 17. Owner-pending amendment (2026-10-06 compliance memos)
Two memos landed after the first draft and bind this spec once the owner confirms. Neither changes the data model; both change wording rules and one CTA.
- **Tours exposure memo (2026-10-06).** The reserved trips slot on the destination page (section 6.13, filled by SP2) must: (a) use "trip idea" or "itinerary" wording and never "package" in any market's copy, headings, alt text or WhatsApp prefill; (b) print only per-service, dated supplier figures (hotel public rate with date, published rail fare with date, transfer quote with date), never a combined trip price, never "from {amount} per person"; (c) use option (a) referral framing with the single CTA "Ask about this itinerary", enquiry-only, no deposit, no commission language. CopyRules gains the pattern `\bpackage(s)?\b` for `.com` surfaces once SP2 wires the slot, and the SP2 test suite asserts no combined price string appears in the block. Cited: tours deep dive sections 4 and 6h (PTR 2018 reg 2 "package" definition; Breakout opt-out line), US+CA deep dive section 4 (seller-of-travel reach triggered by "arranges" and advertising for consideration; zero-fee referral sits outside every definition quoted).
- **UAE licensing memo (2026-10-06).** Any AE price line, whether in `partials.market-price`, the fee table, the Offer schema or the `.md` twin, follows the VAT ruling: do not print "incl. 5% VAT" (or any VAT-inclusive claim) unless Beyond Passports is UAE VAT-registered; until then the AE price, when set, is shown as the bare AED figure with the existing "our service fee, all in, per applicant" line and no VAT wording. Effect on this spec: `partials.market-price` adds no VAT text for any market (it has none today; this locks it), the UAE deep dive's "AED 649 incl 5% VAT" recommendation is not carried into config copy, and `MarketCountrySchemaTest` gains an assertion that the AE Offer carries only `price` and `priceCurrency`. Cited: UAE deep dive 7 (price construction note "UAE displays VAT-inclusive" is a market convention, not a licence to claim registration), service brief 10 (DET licensing memo gate), foundation 4.1 (prices null until the owner sets them).
Owner action: confirm both memos apply as written, or amend, before SP2 fills the slot and before any AE price is set in config.

> **Cross-spec note (added when saving, 2026-10-06):** the SP5 payments spec currently sets `tax_mode = inclusive` and `tax_note = "Prices include 5% UAE VAT."` for AE (SP5 Task 1). That conflicts with the UAE memo rule above. Until the owner rules, SP5's AE `tax_note` must ship as `null` (tax_mode `none`), and the SP5 config comment already carries caveat A6. Resolve in the SP5 plan before Task 1 runs.
