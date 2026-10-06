# Beyond Passports product goals (locked 2026-10-06)

> **Decision basis:** this file + `docs/superpowers/research/2026-10-06-README-decision-basis.md` (14 reports). Owner-locked 2026-10-06.

Main goal: beat the competitor set. Rule: every decision is competition-led. Source: `docs/superpowers/research/2026-10-06-competition-led-gap-analysis.md` + five research reports (same date). Each goal names the lane, who we beat, how, and the measurable target.

## G1. Human-plus-monitoring appointment service
Lane: slot monitoring / auto-book (Visard, Visa Catcher, VisaD, Atlys widget) + appointment-as-product (WE ARE LONDONERS, Toronto micro-agencies, AE "priority appointment" agencies).
Gap: bots have no humans; agencies have no bots; nobody fills both.
Goal: per-market live availability board + named consultant on WhatsApp. Honest framing only: "we watch, you book, we prepare". No dated promises, no "guaranteed appointment", no fast-track claims (VFS publicly says none exists).
Steal: Visard pay-after-success model (£90 only after appointment secured = BP's split, say it like Visard), VisaD "What we don't do" section + outcome refund.
Target: live board with real "last checked" stamp in every launched market; one logged VAC check per core destination before a market opens.

## G2. Transparent-price document consultancy (core)
Lane: FlyFast/Breakout (£60-140 tiers, strike-through discounts), Tourloom (hidden), Flypass, Aviva (£385), Green Apple/Regal/GlobalVisaShop/AFC/Akira (AED 450-899), One Visa World/Visa Logistics (hidden), The Visa Agent (R1,590).
Gap: hidden totals (ZA, US, CA, half of UK), no registration numbers, refund buried in T&Cs, none publish an ICO number.
Goal: one all-in service fee per market in local currency, govt/VAC fee shown separately, "payable now / after appointment" split, refund rule on the service page, CH 17331903 + ICO ZC197159 + local data-law statement, "you may apply directly via the official site" beside the price.
Steal: xVisa laddering, Aviva "(including ... plus our fees)" parenthetical, Breakout 14-day/refund-before-work policy, GoVisa/iVisa "apply directly" line.
Target: price visible above the fold on every money page in every market; zero "Get a quote" placeholders on .com.

## G3. Programmatic market x destination pages with structural SEO edge
Lane: Atlys (2,000 product URLs, llms.txt, .md twins, AI-crawler Allow), iVisa (970 pairs, richest schema), Visard (77 corridors, hub-linked, dated), VisaHQ (89k thin).
Gap: nobody does hreflang on destination pages; nobody server-renders fee + VAC tables with numbers; nobody exposes live availability as crawlable text; nobody has a named reviewer per page.
Goal: 4 markets x 29 destinations on beyondpassports.com as new data-driven templates, 3,000-4,500 unique words each, server-rendered fee + VAC-operator tables, documents table with "common mistake" column, 10-15 FAQs in FAQPage schema, iVisa schema floor (Organization, Service/Offer, AggregateRating, BreadcrumbList) + Visard additions (Service, Country, Audience, dateModified), full reciprocal hreflang incl .co.uk + x-default, AI-crawler Allow, sectioned llms.txt, per-page Markdown twin.
Avoid: Atlys IP 307, "for Indians" fallback, on-time guarantees; iVisa visa-free filler pages; GoVisa machine-translated sprawl.
Target: 116 pages indexed; top-10 for "schengen visa {country} {market}" long-tail within 6 months; >=40% impression share per launched market within 90 days of spend.

## G4. Visa-led tours on the visa page
Lane: Musafir (AE, AED 7,999-9,299, "guaranteed appointment"), iVisa travel-packages (€2,890, separate catalogue), Tourloom cards (hidden, no flights), FlyFast/Breakout "holiday" cards (visa fees in disguise), diaspora operators US/CA.
Gap: nobody sells Schengen visa-led trips to ZA/AE/US/CA residents; nobody puts the trip on the visa page; nobody itemises ground transport.
Goal: tours block on each market x destination page + `/tour-packages` per market. Card anatomy: {Country} · {N nights} · {cities} · hotel tier · named ground transport · "visa preparation included" · "flights not included, you book, we match the itinerary". Indicative "from {local currency} per person", confirmed on WhatsApp. Enquiry-only. Compliance strip: separate services, no flights, not ATOL-protected, availability set by the visa centre.
Steal: Tourloom card anatomy; Breakout "not the organiser of a package holiday" line (if memo supports); iVisa "not included" transparency.
Gate: PTR 2018 + per-market travel-seller memos (US states, CA provinces, UAE DET, SA CPA) before launch; phase 1 may be referral to licensed local operators.
Target: tours block live in UK + first launched market; tour enquiry rate tracked per market in the Clarity sheet.

## G5. Per-market trust and proof
Lane: AE agencies (DET/DED licence + IATA TIDS on page, Google 1,500+ reviews), ZA agencies (Google 1,280 reviews, offices), US (BBB, embassy accreditation), Visard (Companies House in schema.org), IAS (regulator number).
Gap: CH/ICO mean little abroad; no competitor publishes ICO; ASATA/TICO untapped; Trustpilot is UK-only.
Goal: per-market trust strip (UK-registered + data-protection registered + "look us up" + POPIA/PDPL/CCPA/PIPEDA statement + local review platform), Companies House + ICO in Organization schema on every page, per-market review collection (Google everywhere, Hellopeter ZA, BBB US).
Target: trust strip + schema on every .com page at launch; review collection live per market within 30 days of first client.

## G6. Fix the UK auction first
Lane: Atlys 56% IS vs BP 12.4%, budget-limited at £25/day; Tourloom (sister brand) 18.8% outbidding BP; FlyFast+Breakout one operation.
Goal: confirm Tourloom relationship, split keywords/geos or consolidate budget; raise budget or cut keywords per lost-IS data; win the Flypass copy race on France/country pages (competitor-swot.md approved section stack).
Target: UK IS back to >=19% (Aug level) before new-market spend; combined BP+Tourloom share not bidding against itself.

## Market gap map (locked 2026-10-06)
| Market | Who targets it | What they do | Gap left open |
|---|---|---|---|
| South Africa | No global (Atlys no en-ZA, iVisa no ZA pair, Visard no /za). Locals: One Visa World, Visa Logistics, Visa24HR, Visa Box, The Visa Agent, IAS thin subdomain | Offline agencies: offices, Google reviews, in-person accompaniment, quote-on-enquiry | Everything digital: pricing (one agency shows R1,590), slot monitoring, VAC routing across five operators (VFS incl. Switzerland / TLScontact DE+BE / BLS ES / Capago FR / GVCW GR; Visalink is a stale reference, 2026-10-06 SA deep dive), ASATA, Hellopeter VFS anger. Most open per demand data. LAUNCH FIRST before Atlys adds en-ZA |
| UAE | Atlys hardest (en-AE strongest, 198 URLs, Dubai store, AED 108+499 split). Visard AED 200. ~12 licensed agencies AED 450-899. Musafir tours + "guaranteed appointment" | Visible prices, WhatsApp-first, dummy letters, licence numbers, tours bundled | Honest appointment framing (VFS says no fast-track), named humans + monitoring, refusal-aware consulting. Most saturated; wedge = honesty + board |
| USA | Atlys thin (US France page = "for Indians" H1). CIBT/VisaHQ hidden fees. Visard pay-after-success, Telegram bots. WE ARE LONDONERS LTD dated-appointment lookalike network | Bots + corporates; no national human brand for non-citizen residents | Resident-non-citizen specialist (green card/H-1B/F-1/EAD rules) at transparent price; human + monitoring; honest counter to "7 working days" promises; visa-led tours |
| Canada | WE ARE LONDONERS, two Toronto micro-agencies (broken SSL, "Early Appointment CAD 150-180"), schengenvisapro, visahq.ca, Y-Axis. No Atlys/iVisa product | Tiny, dated, phone-first, no trust marks; consulates call paid agents scams | Nearly everything: no TICO/CPBC/OPC claim, no French Quebec service, no monitoring. Most open structurally, smallest demand |
Pattern: globals built pages not service; locals built service not pages; nobody built both; nobody puts tours on the visa page. Movers: Atlys locale-by-locale (ZA missing), IAS za./us. + Lagos, WE ARE LONDONERS live in US/CA.

## Competitor targeting map (locked 2026-10-06)
Full data: docs/superpowers/research/2026-10-06-competitor-targeting.md.
- UK paid intents: Core (~50k vol) GoVisa/Breakout/TourLoom/FlyPass; UK-geo (~40k) GoVisa 16 kw, FlyPass 7, BP ZERO; Per-country (~10k) GoVisa alone 30 kw; Appointment (~8k) Atlys + TourLoom + GoVisa; Agency/service (~2k) 3-5 bidders per term = where BP sits.
- Destinations x markets: Atlys UK 23 (no Italy/Spain/Portugal/Greece/Denmark/Iceland), UAE 28, US 28 (no Switzerland, India leaks), Canada 0, ZA none. iVisa 10 per origin UK/US/CA, 9 UAE, ZA 0. Visard UK 21, UAE 11 low-demand, US 4, CA 0. Boutiques 9 each UK-only.
- Open targets: UK-geo segment; UK per-country terms; Atlys-blank UK Italy/Spain/Portugal/Greece; Canada 19/29 destinations with no page from anyone; ZA every destination; UAE big-five vs Atlys + locals only; US long tail; everywhere: tours-with-visa, centre-level appointment pages with live text, refusal-recovery per market.
- G3 build order: CA + ZA all 29 first, then AE/US long tail, then UK Italy/Spain/Portugal/Greece.
- G6 order: enter UK-geo + per-country terms before 5-bidder agency terms; re-enable converters TourLoom account data proves (consultancy 32% CR, agents 30%, application assistance 27%, book appointment 15%) with policy-safe copy.

## Excluded on purpose
- Dated appointment promises / "guaranteed appointment" / fast-track claims: compliance (express-not-faster-govt), ASA, Google Ads Government Documents policy.
- Regulated legal advice (IAS lane): not a law firm; no IAA/OISC claim.
- Flight-inclusive packages: ATOL + PTR insolvency protection not held.
- Price-less "contact for quote" model, fake counters, discount timers.

## Launch order + gates
ZA first, AE second, US + CA as a pair after US-hours reply capacity. Gate per market: one logged VAC check per core destination, one compliance memo reviewed by a local professional, one live local-currency charge end to end.
