# International market foundation (beyondpassports.com) design spec

**Status:** draft for owner review. Written 2026-10-06. Sub-project 1 of 7 in the international + tours programme.
**Decision basis:** `docs/product-goals-2026-10.md` (G1-G6) and `docs/superpowers/research/2026-10-06-README-decision-basis.md` (14 reports). Every design choice below names the file or finding it rests on, per the locked rule "every decision is competition-led".
**Supersedes / extends:** `2026-09-05-south-africa-market-design.md` (SA phase 1). Keeps its locked decisions (market by URL, SA dataset parallel to UK, null-safe pricing) and generalises them to four markets. Refines one: the `.com` root gets a single host-bound route (section 4.4).

## How to use this document
A spec is the agreement on WHAT gets built and WHY, written before any code, so the build can be judged against it. It is not a plan (the plan breaks this into ordered tasks with tests) and not code. Use it like this:
1. Read sections 1-3 first. If the goal, scope or any assumption in section 3 is wrong, say so now; everything after depends on them.
2. For each numbered decision in sections 4-9, ask one question: "does this move a scoreboard line in product-goals, and does the cited evidence support it?" If yes, leave it. If no, strike it or amend it.
3. Approve by saying which sections are approved and which need changes. "Approved" on the whole document unlocks the implementation plan (`superpowers:writing-plans`). Nothing is built until the plan is also approved.
4. After build, the test list in section 11 is the acceptance checklist: every line must pass or the sub-project is not done.
5. To change something later, edit this file first, then the plan, then the code. The file is the source of truth for the team or agent that inherits the work (see memory: scaling-agent-team-plan).

## 1. Goal
Ship the plumbing that lets `beyondpassports.com` serve per-market Schengen pages for South Africa (`/za/`), UAE (`/ae/`), USA (`/us/`) and Canada (`/ca/`) from the existing Laravel app, with the UK untouched on `beyondpassports.co.uk`, so that sub-projects 2-7 (tours catalogue, templates, slots per market, payments per market, compliance/ops, launch) have one market model, one routing scheme, one SEO scheme and one analytics tag to build on.

Success for this sub-project: a market can be switched on by config, shows a market home and hub stub with correct chrome, canonical, hreflang, robots state and analytics tag, and is invisible (404) when off. Scoreboard lines served: G3 (page architecture, hreflang edge), G5 (per-market trust strip), G6 (nothing here spends UK budget).

## 2. Scope
In: markets config, Market resolver, route group, `.com` root, market-aware chrome, canonical/hreflang/sitemap/robots, analytics tag, market home + hub stub + tours route, SA branch merge, `/south-africa` redirect, tests.
Out (own specs later, numbering per the locked decomposition): tours catalogue DB + detail pages (SP2), country page templates and content (SP3), slot pool scoping per market (SP4), payments and local-currency Stripe (SP5), compliance memos and VAC accounts (SP6), DNS/Cloudflare/GSC/Ads launch (SP7). Also out: any price display on `.com` (prices stay null until set in config), any geo-IP logic, any migration of `.co.uk` content.

## 3. Assumptions standing in for the five open owner decisions
Each is a default so the build can proceed. Flip any of them and the affected section changes; none of them blocks the foundation.
- A1 Tourloom is a sister brand (evidence: chat-brain lessons 28 Sep, BP invoices paid to TOURLOOM LIMITED). Effect here: none on code; copy never positions against Tourloom. UK auction split is a G6 action outside this spec.
- A2 No non-UK VAC portal account exists yet (feasibility report premise check 2). Effect: every market ships `enabled=false`; boards on `.com` are snapshot-only and render "We'll check for you" when no snapshot exists; the market stage gate (one logged VAC check) is a launch condition, not a code condition.
- A3 No second ops identity yet. Effect: each market carries `support_hours` text in config, default "UK business hours, replies same day"; the 30-minute promise is not printed on `.com`.
- A4 Tours phase 1 is enquiry-only with no on-page prices; existing catalogue is shown per market with a WhatsApp enquiry CTA. No "package" wording in UK copy (tours deep dive, PTR 2018 reg 2).
- A5 Reply channel per market: WhatsApp everywhere; US/CA also show a phone/SMS line once a number exists (config, null hides it). UAE copy never offers a WhatsApp call (TDRA VoIP block, UAE deep dive).

## 4. Market model and routing
### 4.1 Config shape (`config/ukv.php` key `markets`)
Extends the SA branch's `markets.za` block. One entry per non-UK market; UK is the implicit default with no entry and no prefix.
```php
'markets' => [
    'za' => [
        'label'          => 'South Africa',
        'locale'         => 'en-ZA',
        'currency'       => 'ZAR',
        'currency_symbol'=> 'R',
        'price_total'    => null, 'price_upfront' => null, 'price_remainder' => null,
        'whatsapp'       => null,          // null = config('ukv.whatsapp')
        'phone'          => null,          // optional local/callback number
        'team_label'     => 'South Africa Team',
        'support_hours'  => 'UK business hours, replies same day',
        'positioning'    => 'Your Schengen file, checked line by line, with a named consultant on WhatsApp who actually answers. Price in rand, upfront.',
        'data_law'       => 'POPIA',
        'enabled'        => (bool) env('UKV_MARKET_ZA_ENABLED', false),
        'indexable'      => (bool) env('UKV_MARKET_ZA_INDEX', false),
    ],
    'ae' => [ /* AED, en-AE, 'The only thing we don't sell is a shortcut.', data_law PDPL */ ],
    'us' => [ /* USD, en-US, resident-non-citizen line, data_law CCPA */ ],
    'ca' => [ /* CAD, en-CA, PR/permit-holder line + 'Service en français disponible', data_law PIPEDA */ ],
],
'intl_base_url' => env('UKV_INTL_BASE_URL', 'https://beyondpassports.com'),
```
Positioning lines come from the competitive service brief section 1. Prices stay null (SA spec rule; document-consultancy deep dive: never show a placeholder). `enabled` and `indexable` are separate so `.com` can be staged live but noindex (SEO footprint report: Atlys noindexes non-core locales, we stage deliberately instead).
A `MarketsConfigTest` asserts every entry has every key and that `enabled`/`indexable` default false.

### 4.2 Market value object and resolver
`App\Support\Market`: readonly object with `code`, `label`, `locale`, `currency`, `symbol`, `whatsapp()`, `phone()`, `isUk()`, `isIndexable()`, `baseUrl()`. `Market::uk()` returns the implicit default. `Market::current()` reads the instance bound in the container by middleware, defaulting to UK. No Host header inspection anywhere (SA spec; intl-architecture report: Atlys's IP 307 makes non-core locales uncrawlable).
`App\Http\Middleware\ResolveMarket`: reads the `{market}` route parameter, 404s if the code is not in config or `enabled` is false, binds the Market, and adds `X-Robots-Tag: noindex, nofollow` to the response when `indexable` is false.

### 4.3 Route group
```php
Route::prefix('{market}')->where(['market' => 'za|ae|us|ca'])->middleware(ResolveMarket::class)->group(function () {
    Route::get('/', MarketHomeController::class)->name('market.home');
    Route::get('/schengen-visa', MarketHubController::class)->name('market.hub');       // stub until SP3/SP4
    Route::get('/tour-packages', MarketToursController::class)->name('market.tours');   // existing catalogue, enquiry-only (A4)
});
Route::redirect('/south-africa', '/za', 301);
```
The regex is built from `array_keys(config('ukv.markets'))` at boot so adding a market is one config entry. Country routes `/{market}/schengen-visa/{country}` are reserved for SP3 and not registered here. Pattern mirrors Visard's market-first paths (intl-architecture report, SEO report (d)).

### 4.4 The `.com` root
`.co.uk/` must stay the UK home; `.com/` must show an international chooser. These conflict without one host-aware rule. Decision: one `Route::domain(parse_url(config('ukv.intl_base_url'), PHP_URL_HOST))->get('/', InternationalHomeController::class)` registered before the UK home route. This is the only host-bound route in the app and is documented as such in `routes/web.php`. The chooser lists enabled markets as cards plus a UK card linking to `.co.uk`, and is the `x-default` target (SEO report (d); Visard uses root as x-default, Atlys and iVisa omit it). Alternative rejected: a Cloudflare redirect rule, because it makes local and staging behaviour differ from production.
Manual market switcher in the chrome, remembered in a first-party cookie `bp_market` for 30 days; a dismissible one-line banner suggests the stored market when a visitor lands on another market's page. Never an automatic redirect (intl-architecture report, Atlys weakness).

### 4.5 URL helper
`market_url(string $path, ?Market $m = null): string`. UK: `url($path)` unchanged. Non-UK: `rtrim(intl_base_url,'/') . '/' . code . $path`. All chrome and nav links go through it (section 5). A `MarketUrlTest` covers UK, each market, trailing-slash and root cases.

## 5. Chrome
`partials.lp-chrome`, `partials.lp-footer`, `App\Support\NavService` swap `url()` for `market_url()`. Topbar: `team_label` replaces "UK Team"; WhatsApp link uses the market number with fallback; phone line shows only when `phone` is set (A5). Footer keeps "Registered in England and Wales, Companies House 17331903" (true everywhere) and adds "Serving applicants in {label}". Legal, about and contact remain UK pages, linked cross-domain. Market-specific legal wording waits for the compliance memos (gap analysis A13, A17).
Trust strip on every `.com` page is a new partial `partials.market-trust-strip` fed by config: Companies House number, ICO number, data-law line, "We never sell appointments", `support_hours`. Content per market from the service brief section 7. It reuses the locked `disclaimer-strip` partial underneath, never bare markup.

## 6. Pages shipped here (content is placeholder-honest, design pass is SP3)
- Market home `/{market}`: positioning line as H1, three-sentence honest intro ("We are introducing this service for applicants applying from {label}"), WhatsApp CTA with prefilled text carrying the market code, trust strip, disclaimer strip, links to hub and tours. No price block unless `price_total` is non-null (SA spec rule). No board on the home page.
- Hub stub `/{market}/schengen-visa`: same chrome, H1 "Schengen visa from {label}", a "We'll check for you" availability block (A2), WhatsApp CTA, and a static list of the 29 Schengen destinations as text (no links until SP3). The real board arrives in SP4; the SlotBoard dynamic pool is never called on `.com` routes (appointment deep dive: snapshot-only is the honesty wedge).
- Tours `/{market}/tour-packages`: existing `tours-body` partial, market chrome, enquiry CTA with package name + market in the WhatsApp text, no prices, the word "package" replaced by "trip" in UK-facing strings only when the UK template is later unified (A4; this spec does not edit UK copy).
- International chooser `.com/`: market cards + UK card; `x-default`.

## 7. SEO
- Canonicals: UK pages canonical to `.co.uk`, market pages to `intl_base_url`, computed from the Market, never from the request host. Either host can serve either page without duplicate-content penalty.
- hreflang partial `partials.hreflang` on: UK home, UK `/schengen-visa`, UK `/tour-packages`, and their market equivalents. Emits `en-GB` (`.co.uk`), each enabled and indexable market, and `x-default` (`.com/`). Reciprocity holds because the UK pages include the same partial (SEO report (d); gap analysis A8: no competitor does this on destination pages).
- Sitemaps: existing `/sitemap.xml` unchanged. New `/sitemap-intl.xml` lists `.com` URLs for enabled and indexable markets with real `lastmod`. Static `robots.txt` lists both sitemap URLs. Cross-host sitemap references are valid once both properties are verified in Search Console (SP7).
- Robots: non-indexable markets get `X-Robots-Tag: noindex, nofollow` from the middleware and are excluded from sitemap-intl and hreflang.
- Future-proofing for G3: the page templates in SP3 will add `Organization` schema with Companies House and ICO identifiers (Visard puts its company number in schema; SEO report (b)) and the AI-crawler `Allow` block in robots (Atlys pattern). This spec adds the robots block now since it is one static file edit.

## 8. Analytics and attribution
`partials.analytics-head` pushes `{ bp_market: '<code>' }` to the dataLayer before GTM loads. The existing lead attribution (gclid/UTM, `(Ref: BP-XXXXX)`) and CRM beacon gain a `market` field. WhatsApp prefilled text on `.com` carries `[{CODE}]` after the ref so the Lead Chats tab can be filtered per market (lead-chats-log rule). Clarity segmentation by `bp_market` is configured in SP7.

## 9. Merge and migration
Merge `feat/sa-market-phase1` first (6 commits, 0 conflicts). Keep its migration, `AvailabilityService($market)` param, Filament market selector and tests. Delete `public.lp-south-africa` view after the market home template replaces it, and point its test at `/za`. `/south-africa` 301s to `/za`.

## 10. Error handling
Unknown market code: 404. Disabled market: 404. Indexable false: served with noindex header. Missing price: block hidden. Missing WhatsApp: fallback to global number. Missing `intl_base_url`: `market_url` throws a clear configuration exception at boot in non-production and logs in production. Hreflang partial on a page with no market equivalents renders nothing.

## 11. Testing (acceptance checklist)
Feature tests, `php artisan test --filter=Market`:
1. Disabled market 404s; enabled market 200s; unknown code 404s.
2. Indexable false sends `X-Robots-Tag: noindex, nofollow`; indexable true does not.
3. `market_url` for UK, each market, root and nested paths.
4. Market home shows positioning line, trust strip, WhatsApp link with market number (or fallback) and no price when null; shows price when set (reuses the SA test pattern).
5. Hub stub shows "We'll check for you" and never calls `SlotBoard`.
6. Tours route renders the catalogue with market chrome and no prices.
7. Canonical host per page type (UK page on `.com` host still canonicals to `.co.uk`).
8. hreflang reciprocity: UK hub lists en-ZA when ZA is enabled and indexable, omits it otherwise; ZA hub lists en-GB and x-default.
9. `/sitemap-intl.xml` lists only enabled and indexable markets; `/sitemap.xml` unchanged.
10. `/south-africa` 301 to `/za`.
11. dataLayer contains `bp_market`.
12. `MarketsConfigTest`: every market has every key; defaults false.
13. `.com` root route serves the chooser; `.co.uk` root unchanged (test by setting `intl_base_url` and the request host).
Existing suites (`AvailabilityServiceTest`, `LpFlow*`, `LpAssemblerTest`) must stay green.

## 12. Competitor citation per decision (the locked rule, in one table)
| Decision | Beats / learns from | Source |
|---|---|---|
| Market-first path prefix | Visard `/uk` `/uae` `/usa` cluster; avoids VisaHQ ccTLD sprawl | intl-architecture, SEO footprint (a),(d) |
| No geo-IP, manual switcher + cookie | Atlys IP 307 noindexes non-core locales | intl-architecture (Atlys), SEO footprint |
| x-default to `.com` chooser | Atlys and iVisa omit x-default; Visard uses root | SEO footprint (d) |
| hreflang on every page incl. `.co.uk` reciprocals | nobody does it on destination pages | gap analysis A8 |
| enabled/indexable split | stage without the Atlys "generated but noindexed" mess | SEO footprint (Atlys) |
| Prices null until set, never placeholder | FlyFast/Breakout "£150 holiday" cards; Tourloom "transparent" with no number | visa-tour-bundling, document-consultancy lane |
| Trust strip with CH + ICO + data law | no competitor publishes ICO; AE/ZA/US trust marks differ | gap analysis A13, service brief 7 |
| "We never sell appointments" line | VFS "no fast-track"; BLS "no intermediaries"; Italy Toronto "it is a scam" | appointment lane, UAE, US+CA deep dives |
| Snapshot-only board, dynamic pool off on `.com` | Visa Catcher "last seen X ago" honesty; Atlys/Visard credibility gaps | appointment lane section 4, 7 |
| No WhatsApp-call offer in UAE | TDRA VoIP block | UAE deep dive section 4 |
| `support_hours` per market, no 30-min promise on `.com` | US/CA evening = UK night; UAE 9am-9pm GST norm | feasibility gap 6, US+CA deep dive |
| Tours enquiry-only, no "package" wording | PTR 2018 reg 2; Breakout opt-out line; Musafir "guaranteed appointment" bundle | tours lane sections 4, 6h |
| Market tag on leads | per-market Clarity and Ads segmentation from day one | feasibility, lead-chats-log rule |

## 13. Open items carried to later specs
Tours catalogue in DB with Filament + detail pages (SP2); country templates and the operator matrix (SP3); SlotBoard per market and snapshot ingestion for non-UK VACs (SP4); Stripe presentment currency, `orders.market`, invoices, tax memos (SP5); compliance memos per market, VAC accounts, second ops identity (SP6); DNS, SSL, Cloudflare (bot-fight lesson), GSC properties, Ads geo campaigns, Quebec French mirror before Quebec ads (SP7).

## 14. Amendment proposed after approval (owner to accept or reject)
Source: `docs/superpowers/research/2026-10-06-tours-travel-seller-exposure-memo.md` (2026-10-06). PTR 2018 reg 2 limb (b)(iii) makes a card "advertised or sold under the term 'package' or under a similar term" a package by definition, and no PTR definition turns on commission. Proposed changes to this spec and to plan Task 7: (1) public slug `/{market}/tour-packages` becomes `/{market}/trips` (UK `/tour-packages` untouched here; SP2 decides the UK rename); (2) page copy uses "trip idea" / "itinerary", never "package" or "tour package"; (3) WhatsApp CTA text says "Ask about this itinerary" and the consultant script is option (a) referral (introduce a named licensed operator, transmit no client data, never two supplier links in one chat); (4) the hreflang/sitemap path for trips follows the new slug. Until accepted, Task 7 ships as written but behind the market `enabled` flag, which is false by default.
