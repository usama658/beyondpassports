# Visa-led tours catalogue (sub-project 2) design spec

**Status:** draft for owner review. Written 2026-10-06. Sub-project 2 of 7 in the international + tours programme.
**Decision basis:** `docs/product-goals-2026-10.md` (G4, market gap map, "Excluded on purpose"), `docs/superpowers/research/2026-10-06-competitive-service-brief.md` section 6, `docs/superpowers/research/2026-10-06-deepdive-tours-lane.md` (card anatomy 6b, price construction 6c, compliance strips 6d, FAQ 6e, WhatsApp flow 6f, exposure flags 6h). Every decision names the competitor or finding it answers, per the locked rule "every decision is competition-led".
**Builds on:** `2026-10-06-international-market-foundation-design.md` (SP1): `App\Support\Market`, `market_url()`, `/{market}` route group, `partials.market-trust-strip`, `partials.hreflang` + `MarketAlternates`, `SitemapIntlController`. SP1 Task 7 shipped an interim config-driven `/{market}/tour-packages`; this spec replaces it.
**Replaces:** the `config('ukv.tours.packages')` array and the overlay-card section of `partials.tours-body`.
**Pending amendments:** see the final section (travel-seller exposure memo, owner-pending). Where that section conflicts with sections 4-12, the memo wording wins once the owner confirms.

## How to use this document
1. Read sections 1-3. If the goal, scope or a gate in section 3 is wrong, say so first.
2. For each decision in sections 4-12 ask: does it move a G4 scoreboard line, and does the cited evidence support it? Keep, strike or amend.
3. "Approved" on the whole document unlocks the plan `docs/superpowers/plans/2026-10-06-sp2-tours-catalogue.md`. Nothing is built until the plan is approved too.
4. Section 14 is the acceptance checklist after build.
5. To change anything later: edit this file, then the plan, then the code.

## 1. Goal
Turn the six hard-coded trip cards into a database catalogue that ops edit in Filament, served as a UK list + detail page and as a per-market list + detail page on `beyondpassports.com`, with the honest card anatomy from the tours deep dive (named ground transport, flights excluded, price only when the owner has set and dated it for that market), a per-market compliance strip, an enquiry-only WhatsApp CTA tagged with trip and market, FAQPage schema, reciprocal hreflang and sitemap entries.

Scoreboard lines served: G4 ("tours block live in UK + first launched market; tour enquiry rate tracked per market"), G5 (trust strip on every `.com` page), G3 (hreflang + schema footprint). Nothing here spends UK ad budget (G6 neutral).

Success: a trip can be created, priced per market and published in Filament with no deploy; the UK page and each enabled market page render it with the 6b card; an unpriced market shows no number and no placeholder; a priced market shows "From {local} per person, two sharing, {season}" plus "Indicative, checked {date}"; every CTA opens WhatsApp with `[TRIP:{slug}] [{CODE}]`; no UK-facing copy contains the word "package"; no page offers payment or flights.

## 2. Scope
In: `tour_packages`, `tour_package_destination`, `tour_package_prices` tables; `TourPackage` + `TourPackagePrice` models; seeder migrating the six config trips; `TourPackageResource` (Filament, Catalogue group); UK `/tour-packages` rewrite (same URL, same CMS locked-include) and new `/tour-packages/{slug}`; `/{market}/tour-packages` rewrite and new `/{market}/tour-packages/{slug}`; shared partials `tour-card`, `tour-compliance-strip`, `tour-faq`, `tour-schema`, `tour-detail-body`; `TourFaqs`; compliance texts in config; hreflang `only` filter on `MarketAlternates`; UK sitemap + sitemap-intl entries; tests.
Out: the tours block on market x destination pages (SP3 consumes `TourPackage::listFor()` and `partials.tour-card`); any payment, deposit or checkout for trips (SP5 is visa fees only, never trips); flights in any form; supplier contracts and the price-construction spreadsheet (ops, 6c); the legal memos themselves (SP6); photography beyond the six existing `/assets/tours/*.jpg`; Quebec French mirror (SP7).

## 3. Gates: build versus launch
Build gates (must be true to start the plan): SP1 merged on the working branch (`Market`, `market_url`, trust strip, hreflang, sitemap-intl present). Nothing else.
Launch gates (must be true before a market's trips are priced, indexed or linked from nav; they do NOT block the build):
- L1 PTR 2018 lawyer read of the UK compliance text and the "separate supplier lines, paid separately" model (deep dive 4, 6h; brief 6). Until then UK trips carry no price (owner leaves `price_from` null) and the page stays enquiry-only, which it is by construction.
- L2 Per-market travel-seller memos: US seller-of-travel reach (CA/FL/WA/HI; "arranges, or advertises that he or she can arrange"), Canada TICO/OPC/BC, UAE DET outbound tour-operator licensing, SA CPA pricing disclosures (brief 10, deep dive 6h). Until a market's memo is logged, its trips stay unpriced; the compliance text for that market is the honest "not registered" wording below and is config-editable without a deploy.
- L3 Ops has the price-construction sheet (deep dive 6c) and at least one logged hotel rate + rail fare per trip before entering any `price_from`.
Phase 1 is therefore enquiry referral with no price, exactly the state product-goals G4 allows ("phase 1 may be referral to licensed local operators").

## 4. Data model
### 4.1 `tour_packages`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `slug` | string(140) unique | URL key on every host |
| `name` | string(120) | "Italy Highlights" |
| `where_line` | string(160) | display line "Rome · Florence · Venice". Named `where_line` because `where` is a reserved SQL word |
| `nights` | unsigned tinyint | total nights; card shows "{N} nights" (Tourloom shows "days"; 6b uses nights) |
| `cities` | json nullable | `[{"name":"Rome","nights":2}, ...]`; renders "Rome 2 · Florence 2 · Venice 2" |
| `hotel_tier` | string(160) nullable | "4-star central, breakfast included" (6b "Hotels:" line) |
| `transport` | json nullable | named legs `[{"mode":"Frecciarossa 2nd class","from":"Roma Termini","to":"Firenze S.M.N."}]`; renders "Frecciarossa 2nd class, Roma Termini to Firenze S.M.N.; ..." (deep dive 6a: "transfers" alone reads as vapour, Rayna/Tourloom) |
| `highlights` | json nullable | list of strings for the detail page |
| `image` | string nullable | `/assets/tours/italy.jpg` |
| `flag_css` | string nullable | CSS gradient carried over from config for the card flag chip |
| `main_destination_id` | FK destinations nullable, nullOnDelete | the consulate that receives the application (main-destination rule); renders "({Italy} consulate)" on the 6b "Visa:" line |
| `markets` | json nullable | availability, e.g. `["uk","za","ae","us","ca"]`; `uk` included explicitly |
| `sort` | unsigned smallint default 100 | manual order |
| `published` | boolean default false | unpublished = 404 everywhere, excluded from sitemaps and hreflang |
| timestamps | | `updated_at` is the sitemap `lastmod` |

`tour_package_destination` (`tour_package_id`, `destination_id`, composite PK): every country the trip enters, so SP3 can list trips on a market x destination page with one query.

### 4.2 `tour_package_prices` (one row per trip per market)
| Column | Type | Notes |
|---|---|---|
| `tour_package_id` | FK cascade | |
| `market` | string(2) | `uk`, `za`, `ae`, `us`, `ca`; unique with `tour_package_id` |
| `price_from` | decimal(10,2) nullable | land-only, per person, two sharing, in the market currency |
| `season_label` | string(120) nullable | "May and June departures; peak dates higher" |
| `fx_note` | string(160) nullable | "EUR 1 = R 20.10 on 1 Oct 2026" (6c step 7: convert once at a stated rate) |
| `price_checked_at` | date nullable | REQUIRED whenever `price_from` is set (Filament validation); rendered as "Indicative, checked {date}" (6c step 6: none of 20 competitors date their price) |

Display rule: a price renders only when BOTH `price_from` and `price_checked_at` are non-null for the current market. Otherwise the card shows "Price quoted on WhatsApp for your dates" and nothing else (SP1 rule: null renders nothing, never a placeholder number; FlyFast/Breakout "£150 holiday" cards are the anti-pattern).
A separate table rather than JSON on `tour_packages` because `price_checked_at` must be a real date for the Filament "stale after 90 days" badge (mirrors `destinations.facts_checked_at` + `review_interval_days`) and so a market's prices can be bulk-nulled when a memo expires. See the amendments section: the memo requires per-service figures, which means this table gains a `service` column (hotel | transport) and the unique key becomes (trip, market, service).

### 4.3 Model API (`App\Models\TourPackage`)
`scopePublished`, `scopeOrdered` (sort, name), `availableIn(Market $m): bool`, static `listFor(Market $m): Collection` (published, ordered, eager-loaded, filtered in PHP by `availableIn`; portable across MySQL and sqlite, catalogue is tens of rows), `priceFor(Market $m): ?TourPackagePrice` (applies the display rule), `citiesLine()`, `transportLine()`, `enquiryMessage(Market $m)`, `url(Market $m)` (via `market_url`). `TourPackagePrice::isStale()` = `price_checked_at` older than `config('ukv.tours.price_stale_days')` (90).

## 5. Routes and controllers
UK (`.co.uk`, no prefix):
- `GET /tour-packages` unchanged: `CmsController::pageOrCoded('tour-packages', 'public.tours')`, name `tours`. The CMS locked-include `tours-body` keeps working because the partial itself is rewritten; the golden test (`ContentPagesGoldenTest`) still holds.
- `GET /tour-packages/{slug}` new: `TourPackageController::show`, name `tours.show`, view `public.tour-show` (extends `layouts.public`).
Markets (inside the SP1 `/{market}` group, after `ResolveMarket`):
- `GET /{market}/tour-packages`: `TourPackageController::index`, name `market.tours` (replaces SP1 `MarketToursController`, which is deleted).
- `GET /{market}/tour-packages/{slug}`: `TourPackageController::show`, name `market.tours.show`, view `market.tour-show` (standalone page with `lp-chrome`, `market-trust-strip`, `lp-footer`, same pattern as `market/tours.blade.php`).
One controller reads `Market::current()` (UK default), so the 404 rules are identical on both hosts: unknown slug, `published = false`, or market not in `markets` all 404.
URL path: this draft kept `/tour-packages` as a technical identifier with ad history (G6). The amendments section overrides this: the memo reads PTR 2018 reg 2(5)(b)(iii) as catching anything "advertised or sold under the term 'package' or a similar term", which a URL slug arguably is. Pending owner confirmation the public slug becomes `/trips` on both hosts with 301s from every `/tour-packages*` URL; route names and the `tour_packages` table name stay.

## 6. Card template and copy rules
`partials.tour-card` (`$package`, `$market`) renders the deep dive 6b anatomy, in this order:
```
[flag chip] {name}  (links to the detail page)
{N} nights · {City A} {n} · {City B} {n}
Hotels: {hotel_tier}
Getting around: {transportLine}
Visa: Schengen application prepared by Beyond Passports ({main destination} consulate), fee quoted separately
From {symbol}{X} per person, two sharing, {season_label}      <- only when priceFor() is non-null
Indicative, checked {j M Y}                                   <- only when priceFor() is non-null
Flights not included: you book them, we match the itinerary. Also not included: visa fee, insurance, lunches and dinners, city tax.
[WhatsApp: Ask about {name}]
```
Rules: no em-dashes; no "N services" counters; no "guaranteed", "early", "priority", "fast-track" (Musafir "guaranteed appointment", AFC "98%"); "trip" never "package" in every market (one partial, one rule; UAE/US/CA/ZA copy gains nothing from the word); no "included" claims about flights; no "we book your appointment" (G1 honesty: we watch, you book on the official site). The only places the word "package" may appear in rendered output: the statute name "Package Travel and Linked Travel Arrangements Regulations 2018" inside the UK compliance strip (and, until the slug rename is confirmed, the URL path). A test enforces this on the visible text of every tours page.
The same partial feeds the UK grid, the market grid and (SP3) the destination-page tours block, so the anatomy cannot drift between hosts (Tourloom cards vs FlyFast/Breakout cards differ page to page; ours do not).

UK index page (`partials.tours-body`) section order is kept (hero with eligibility form, how it works, trips grid, proof band, FAQ, CTA band) with copy changes: hero "Visa first. Then the trip." and no "one booking" (single point of sale is a PTR trigger, deep dive 4); step 02 says you book the appointment on the official site; grid section renders the cards and the UK compliance strip; FAQ section is the eight 6e questions with FAQPage schema; CTA band copy "Start with the visa. The trip follows." The proof band is untouched.

## 7. Compliance strip per market
`partials.tour-compliance-strip` (`$market`) includes the locked `partials.disclaimer-strip` with `['text' => config("ukv.tours.compliance.{$market->code}")]`, never bare markup (memory: disclaimer-strip-partial). Texts live in `config/ukv.php` so counsel edits (launch gates L1, L2) are a config change. Drafts (deep dive 6d, honest "not registered" branch chosen for US/CA because phase 1 is zero-fee enquiry referral, which sits outside every seller-of-travel definition quoted in brief 6):
- uk: "Beyond Passports Ltd prepares your Schengen visa application. Hotels and ground transport shown are indicative, priced separately from the visa service, and arranged on enquiry with named suppliers. Flights are not included and we do not sell flights, so bookings are not ATOL protected. Where hotel and transport are booked together through us, the Package Travel and Linked Travel Arrangements Regulations 2018 apply and we will tell you who the organiser is and how your money is protected before you pay. Appointment dates are set by the visa centre, not by us. Visa decisions are made solely by the consulate."
- ae: same first two sentences, then "We do not sell flights. We are not a UAE-licensed tour operator; hotels and transport are arranged through licensed partners named in your quote. Visa appointment availability is controlled by the visa centre (VFS Global, BLS or TLScontact), not by us. Visa decisions are made solely by the consulate."
- za: uk text minus the PTR sentence, plus "Prices are indicative in rand, converted from euro supplier rates on the date shown. We are not ASATA members. Travel insurance is required for every Schengen application."
- us: "... We do not sell flights or air-inclusive trips. We are not registered as a seller of travel in any US state; hotel and transport bookings are made by you directly with the named supplier, or through a licensed local operator we introduce. Appointment dates are set by the visa centre or consulate, not by us. Visa decisions are made solely by the consulate."
- ca: us text with "We are not registered with TICO; the Ontario travel industry compensation fund does not apply to arrangements made through us." in place of the US sentence.
Beats: every UK visa-led clone (FlyFast/Breakout/Tourloom/EuroPath show no PTR/ATOL language); matches iVisa's explicit organiser statement in clarity while saying the opposite (we are not the organiser). The amendments section requires these texts to describe mechanics (who you book with, what we send, what we do not do) rather than deny a legal status; the "Where hotel and transport are booked together through us" sentence is struck under option (a) because nothing is booked through us.

## 8. Enquiry-only WhatsApp CTA
Every card and detail CTA is `Market::current()->chatUrl($package->enquiryMessage($market))`. UK resolves to the global `ukv.whatsapp` number (same as `SiteStats::chatUrl`), markets to their number or fallback (SP1). Message: "Hi Beyond Passports, I am interested in the {name} trip ({N} nights, {cities}) from {market label}. I need a Schengen visa. [TRIP:{slug}] [{CODE}]". `[CODE]` is upper-case, UK included, so the Lead Chats tab filters tour enquiries per market (G4 target "tour enquiry rate tracked per market"; lead-chats-log rule). The consultant then runs the 6f flow (visa-first qualification, appointment-feasibility read, one-screen quote with each supplier line and refundable-until date, pay visa service now, hold refundable hotels, transport after visa). No `<form action>` posts, no Stripe, no deposit anywhere on tours pages; `config('ukv.tours.enquiry_only')` is hard-coded `true` and a test asserts no `/checkout` or `stripe` string on any tours page (EuroPath exposure: flight-inclusive checkout with no ATOL). The 6f "one-screen quote with each supplier line" step is replaced by the option (a) referral script in the amendments section.

## 9. FAQ and schema
`App\Support\TourFaqs::for(Market $m)` returns the eight 6e pairs with the market currency and (UK only) the ATOL sentence substituted:
1 Is the trip price the same as the visa fee? 2 Do you book flights? 3 Can I pay for the trip online? 4 What happens to the hotel if my visa is refused? 5 Will the hotel reservation help my visa? 6 When should I enquire? 7 Can I change dates to match my appointment? 8 Can you arrange halal, vegetarian or Indian meals, or family rooms?
`partials.tour-faq` renders the accordion (existing `.tr-faqd` style) and, when `schema => true`, an inline FAQPage JSON-LD built from the same array (precedent: `public/schengen-visa.blade.php` builds `$faqs` once for accordion and JSON-LD). FAQPage is emitted on the two index pages only (UK and market); detail pages show the accordion without FAQPage and emit `BreadcrumbList` (Home > Plan a trip > {name}) via `partials.tour-schema`, so the same eight questions are not marked up on six-plus URLs per host. No Offer/price schema (never advertise a price the page itself does not show; prices are per market and often null).

## 10. SEO
- Canonical: UK pages `url('/tour-packages[/{slug}]')`; market pages `market_url(...)` from the Market, never the host (SP1 section 7). Paths change to `/trips` if the amendment is confirmed.
- hreflang: `partials.hreflang` with `path => '/tour-packages'` on both index pages (already wired for UK by SP1 Task 9) and `path => '/tour-packages/{slug}', only => $package->markets` on detail pages. `MarketAlternates::for(string $path, ?array $only = null)` gains the `only` filter: alternates list only markets that are enabled, indexable and in the trip's `markets`; `en-GB` is omitted when `uk` is not in `markets`; `x-default` unchanged. Reciprocity holds because both hosts call the same partial with the same arguments (gap analysis A8: nobody does hreflang on these pages).
- Sitemaps: `SitemapController` (UK) adds detail URLs for published trips with `uk` in `markets`, `lastmod = updated_at`, monthly, 0.6. `SitemapIntlController` adds `/{market}/.../{slug}` for each enabled + indexable market, published trips available in that market. Unpublished or unavailable trips never appear.
- Robots: market pages inherit SP1 `X-Robots-Tag` when not indexable; UK pages indexable as today.
- Nav label `ukv.tours.nav_label` becomes "Plan a trip" (removes "Tour Packages" from the UK header).

## 11. Filament (ops)
`App\Filament\Resources\TourPackageResource`, group Catalogue, label "Trips", icon `heroicon-o-map`, `HiddenFromEditor` + `AuthorizesByRole` (same as `DestinationResource`). Form sections: Identity (name with auto-slug on create, slug unique, where_line, nights, sort, published toggle, markets CheckboxList of `uk` + `Market::codes()`); Itinerary (cities Repeater name/nights, hotel_tier, transport Repeater mode/from/to with helper "Name the operator and class; 'transfers' alone is not allowed; flights are never a leg", highlights TagsInput, image path, flag_css); Countries (main_destination_id Select relationship, destinations multi-select relationship); Prices (Repeater on `prices` relationship: market Select distinct, price_from numeric, season_label, fx_note, price_checked_at DatePicker required when price_from is filled, helper text "Launch gate: enter a price only after this market's travel-seller memo is logged and the 6c construction sheet row exists"). Table: name, nights, markets badges, published icon, prices count, stale-price count (red badge when > 0), updated_at; filters published, market. Added to `AdminPanelSmokeTest`.

## 12. Seed migration of the six config trips
`Database\Seeders\TourPackageSeeder` (idempotent on `slug`, added to `ProductionSeeder` after `SchengenSeeder` so destination FKs resolve). Mapping from `config('ukv.tours.packages')` to the 6a/6b shape, all five markets, published, no prices:
| slug | nights | cities | main | named transport |
|---|---|---|---|---|
| paris-long-weekend | 3 | Paris 3 | France | private sedan transfer Gare du Nord/CDG to hotel; Seine cruise |
| amsterdam-and-the-rhine | 5 | Amsterdam 3, Cologne 2 | Netherlands | Eurostar 2nd class Amsterdam Centraal to Köln Hbf; KD Rhine day cruise; Schiphol transfer |
| italy-highlights | 6 | Rome 2, Florence 2, Venice 2 | Italy | Frecciarossa 2nd class Rome to Florence to Venice; Fiumicino sedan; Alilaguna water bus |
| greek-islands-escape | 6 | Athens 2, Santorini 2, Mykonos 2 | Greece | Blue Star / SeaJets ferries economy Piraeus to Santorini to Mykonos to Piraeus; airport transfer |
| spain-and-portugal | 9 | Madrid 3, Seville 3, Lisbon 3 | Spain | Renfe AVE Turista Madrid to Seville; Alsa coach Seville to Lisbon; Barajas transfer (6a: no Madrid to Lisbon flight) |
| best-of-western-europe | 13 | Paris 4, Lucerne 3, Florence 3, Rome 3 | Italy | TGV Lyria Paris to Basel + SBB to Lucerne; EuroCity Lucerne to Milan + Frecciarossa to Florence; Frecciarossa Florence to Rome; sedan transfers |
After the seeder exists the config array is deleted (last plan task). Owner backlog, added via Filament, not seeded: UK Swiss Alps Escape 5n and Prague & Vienna 5n (beat Tourloom unpriced 10d); AE Paris & Switzerland 7n, Eastern Europe Trio 7n, Amsterdam-Paris-Switzerland 8n (mirror Musafir 8,999 / 7,999 / 9,299 without flights); ZA Italy by Rail 7n (vs Thompsons R20,365 flight-inclusive), Germany Cities 5n; US/CA Paris & Amsterdam 6n, Italy Grand 8n ("private, no 40-pax minimum, no mandatory tips" vs Global Holidays).

## 13. Error handling
Unknown slug 404. Unpublished 404. Market not in `markets` 404 (UK too). Empty catalogue renders the page with "No trips listed yet; tell us where you want to go on WhatsApp" and the CTA. Missing `cities`/`transport` render empty lines, never "null". Missing main destination drops the parenthetical. Missing image renders the card without the image block. Price set without a date is blocked at form level and, if it reaches the DB, treated as no price. Compliance text missing for a market code falls back to the UK text (never an empty strip).

## 14. Testing (acceptance checklist)
`php artisan test --filter='Tour|MarketTours|MarketSitemap|Sitemap|ContentPagesGolden|AdminPanelSmoke'`:
1. Model: `listFor` filters published + market; `priceFor` null when price or date missing; `citiesLine`, `transportLine`, `enquiryMessage` formats.
2. Seeder: six rows, slugs as section 12, all five markets, no prices, main destinations resolved when SchengenSeeder ran.
3. `TourFaqs`: eight pairs; UK answer 2 mentions ATOL, ZA does not; no banned words.
4. Card partial: price block absent when unpriced; "From R18,900 per person, two sharing" + "Indicative, checked 1 Oct 2026" when priced for ZA; "Flights not included" always; CTA href contains `[TRIP:slug]` and `[ZA]`.
5. UK index: 200 with empty DB; seeded names visible; visible text (tags, scripts, URLs and the statute name stripped) contains no "package"; no "£" figure when unpriced; FAQPage JSON-LD with 8 questions; UK compliance text; no `/checkout`, no `stripe`; `ContentPagesGoldenTest` still green.
6. UK detail: 200; 404 for unpublished, unknown, and `markets` without `uk`; BreadcrumbList; canonical; hreflang with `only`.
7. Market index and detail: 200 when enabled; per-market compliance text (ae "UAE-licensed", za "in rand", us "seller of travel", ca "TICO", none shows "ATOL" except uk); price in local symbol only when set; `[ZA]` tag; trust strip present; 404 for a trip whose `markets` lacks `za`.
8. hreflang: detail alternates exclude markets not in `markets`; omit en-GB when `uk` absent.
9. Sitemaps: UK lists published uk trips only; intl lists per indexable market only trips available there; unpublished never.
10. Filament: index/create/edit render; Livewire create persists a row; price without date fails validation.
11. Existing suites green: `PublicSmokeTest`, `Cms\*`, `Market*`, `LpAssemblerTest`, `AvailabilityServiceTest`.

## 15. Competitor citation per decision (locked rule)
| Decision | Beats / learns from | Source |
|---|---|---|
| Named hotel tier + named transport legs on every card | Tourloom "Boutique" with no transport; Rayna "transfers"; FlyFast/Breakout cards with zero inclusions | deep dive 1, 6a, 6g |
| Price only when set AND dated; "Indicative, checked {date}" | none of 20 competitors date a price; Costsaver/AFC strike-through "from" | deep dive 2, 6c |
| Per-market price rows in local currency, converted once at a stated FX | Musafir/AFC AED, Thompsons ZAR, Global Holidays USD: nobody re-quotes across markets honestly | deep dive 5, 6c step 7 |
| "Flights not included: you book, we match the itinerary" | EuroPath flight-inclusive with no ATOL; dnata/AFC flight bundles; iVisa "not included" transparency | deep dive 1, 4, 6g; G4 |
| Enquiry-only, no checkout, no deposit | AFC online checkout + seat counters; Global Holidays 40% non-refundable; Musafir cancellation ladder | deep dive 1, 2, 6g |
| "trip" never "package" in copy; separate supplier lines | PTR 2018 reg 2 "advertised or sold under the term 'package'"; Breakout "consultancy fees are separate" | deep dive 4, 6h; brief 6 |
| Visa line names the consulate; no appointment guarantee | Musafir "we guarantee your Schengen visa appointment" beside "visa not included"; AFC "98%" | deep dive 3, 6g; product goals excluded list |
| Refundable hotels until visa issued (FAQ 4, 6) | Global Holidays 40% non-refundable deposit; AFC credit note on refusal; Tourloom "no refunds on refusal" | deep dive 1, 3, 6g |
| Per-market compliance strip via locked partial | every UK clone silent on PTR/ATOL; iVisa explicit organiser text; TICO/CST display rules | deep dive 4, 6d, 6h |
| Eight FAQs with FAQPage | Tourloom/FlyFast FAQ-less cards; iVisa schema floor (G3) | deep dive 6e; product goals G3 |
| CTA tagged `[TRIP:slug] [CODE]` | per-market enquiry rate target; Lead Chats filtering | G4 target; deep dive 6f |
| Catalogue in DB + Filament, not config | Atlys/iVisa ship product URLs at scale; config edits need a deploy | G3 lane; SP1 open item |
| Detail page per trip on both hosts with hreflang | iVisa separate catalogue without market pages; nobody hreflangs tours | brief 6; gap analysis A8 |
| Main destination FK + all-destinations pivot | SP3 tours block per market x destination page (G4 "tours block on each market x destination page") | product goals G4 |

## 16. Open items carried forward
SP3: tours block on destination pages using `TourPackage::listFor($market)->filter(destination)` and `partials.tour-card`. SP6: memos L1/L2, price gate ops rule, supplier agreements. SP7: Clarity segment on `bp_market` + `[TRIP:` for the enquiry-rate target; Quebec French copy before CA ads. Ops: price-construction sheet (6c), hotel rate screenshots, FX log; AE fixed-date framing (Eid, National Day) as `season_label` text once prices exist.

## Amendments from the tours travel-seller exposure memo (2026-10-06)
Owner-pending. Each item overrides the matching section above once confirmed; the plan is updated before any code is written.
1. Slug and vocabulary (amends sections 5, 6, 10). PTR 2018 reg 2(5)(b)(iii): a combination "advertised or sold under the term 'package' or a similar term" is a package by definition, so the URL itself is exposure. Public path moves from `/tour-packages` to `/trips` on both hosts (UK rename included): `GET /trips`, `GET /trips/{slug}`, `GET /{market}/trips`, `GET /{market}/trips/{slug}`, with 301s from `/tour-packages`, `/tour-packages/{slug}` and the market equivalents; CMS page slug, `hreflang` path, sitemap paths, nav href and `PublicSmokeTest`/`Cms\*` fixtures follow. Visible copy says "trip idea" or "itinerary", not "trip package", "tour package" or "holiday". Table name `tour_packages`, model names and route-name prefixes stay (technical identifiers, memory brand rule). G6 note: update the Ads final URL for the tours campaign on the same day the 301 ships.
2. Prices (amends sections 4.2, 6, 11). Reg 2(5)(b)(ii): a single combined "from £X" covering hotel plus transport is an "inclusive or total price" and makes the combination a package regardless of wording. If prices are shown at all they are per-service supplier figures, each dated: `tour_package_prices` gains `service` (enum `hotel` | `transport`) and `supplier_name`, unique key becomes (trip, market, service), and the card renders at most two separate lines, "Hotel from {symbol}{X} per person, two sharing ({supplier}), checked {date}" and "Rail and transfers from {symbol}{Y} per person ({operator}), checked {date}", never summed, never a single "From". The display rule (amount AND date) applies per line. Default for phase 1 remains no prices.
3. Enquiry script (amends section 8 and deep dive 6f). Option (a) referral only: the consultant introduces one named licensed operator for the hotel and transport lines, transmits no client data to any supplier (the client contacts the operator), never sends two supplier links in the same chat, and Beyond Passports takes no commission, margin or referral fee on the trip. The visa service is quoted and paid as today. The 6f "one-screen quote with each supplier line and refundable-until date" step is withdrawn; the consultant may state the operator's published refundable-until policy but does not quote figures. `enquiryMessage()` and the Lead Chats tag are unchanged.
4. Disclaimer wording (amends section 7). The compliance strip describes mechanics, never a Breakout-style denial ("we are not the organiser of a package holiday" is struck): who the client books with, what Beyond Passports sends (the visa file; a named operator introduction), what it does not do (sell flights, hold money for hotels or transport, combine services at one price). The "Where hotel and transport are booked together through us, the Package Travel and Linked Travel Arrangements Regulations 2018 apply ..." sentence is removed because nothing is booked through us under option (a); the statute name therefore disappears from rendered output and the "no package word" test loses its statute exception. Per-market US/CA/AE/ZA texts keep their registration facts stated as facts, not disclaimers.
