# beyondpassports.com market home: Atlys critique and layout spec

Status: draft for owner review, 2026-10-07. Scope: `/za`, `/ae`, `/us`, `/ca` home pages. Inputs: visual identity LOCKED, product goals G1 to G6, foundation spec sections 4 to 6, SP3 4.7, SP4 4.4, the 2026-10-06 portal research set, service brief section 4.

Verification. `atlys.com/en-ID` fetched 2026-10-07 confirmed: banner "Apply for Visas Online to 120+ Countries, Guaranteed On-Time Delivery", tabs Explore / Events, the four filters, card fields (flag, Type, Valid, Fees, Documents Needed, "Get emergency assistance"), "No Visa Required" cards, footer "Live Video Call". `atlys.com/en-US` served the Indonesia locale the same day, confirming the geo-lock. Client-rendered, so [unverified in fetch, seen by owner 2026-10-07]: per-card "Guaranteed Visa On 14 Oct 2026, 12:27 AM" stamps, IDR amounts, Events content, footer tools and Trust links.

## Deliverable 1: Atlys home critique

### (a) What works for the customer
1. Search-first. One field, passport flag beside it; "can I go to X?" answered without a menu.
2. Fee on the card before any click. iVisa's top complaint is "You do not see what your purchase costs" (uk.trustpilot.com/review/ivisa.com?stars=1&page=2, 30 Sep 2026); Atlys avoids that shock.
3. Documents Needed on the card sets expectations before commitment.
4. A dated promise. True or not, a date turns a service into a deliverable; it is the page's strongest conversion device (4.4 Trustpilot score beside 19% one-star reviews, uk.trustpilot.com/review/atlys.com, seen 2026-10-06).
5. Tools and a human route. Requirements Checker, Rejection Recovery [unverified in fetch] and the verified Live Video Call give a hesitant visitor an exit other than checkout.

### (b) What fails or misleads
1. "Guaranteed Visa On {timestamp}" for Sticker visas. A Schengen visa is decided by a consulate and booked on a VAC calendar Atlys does not control; its Terms call the ETA and slot "indicative... best effort basis... and not binding" (atlys.com/en-US/terms, seen 2026-10-06). Customer: "Guaranteed by 11 September. Today is 23 September" (Trustpilot, 23 Sep 2026). A minute-level stamp is false precision and fails the ASA and Google Ads tests BP is bound by.
2. "No Visa Required" filler pads the grid and buries paid products (the filler G3 says to avoid).
3. A 120+ grid for a one-country intent. A Johannesburg resident bound for Paris scrolls past Uzbekistan; filters serve inventory, not one trip.
4. Events tab. "Get Visa 6 days before event" [unverified in fetch] is urgency theatre; no consulate moves faster for a MotoGP weekend.
5. "Get emergency assistance" on every card: rescue-framed upsell before the base service is explained.
6. AI first. BOLO handles "38% of customer interactions" (Atlys's figure); the corpus's top complaint is "You can't even talk to a human" (uk.trustpilot.com/review/atlys.com?search=human, seen 2026-10-06); PissedConsumer logs "Issues Resolved: 5%" (atlys.pissedconsumer.com/customer-service.html, seen 2026-10-06).
7. No itemised government fee. One blended "Fees" figure; the fee blog "does not disclose what Atlys itself charges" (atlys.com/blog/schengen-visa-fees, 10 Oct 2025); "£0 for now" became "£144.67 to unlock" (Trustpilot, 5 Nov 2025).
8. Geo-locked locale. en-GB and en-US both resolve to en-ID (fetched 2026-10-06 and 2026-10-07); no en-ZA exists.
9. App-first OTP. "the link forced me to install their app" (Aug 2025), "unable to login via laptop" (28 Aug), both uk.trustpilot.com/review/atlys.com, seen 2026-10-06.

### (c) For a Schengen-only, four-market consultancy
Do not copy: dates on cards, visa-free filler, a grid wider than 29, events, emergency upsell, bot-first chat, blended fees, IP locale, app-gated login.

Steal and do better:
1. Search-first entry, scoped to 29 destinations and the visitor's market, with the "which consulate" rule built in (Atlys has none).
2. Fee before any click, itemised: our fee in two instalments; government and VAC fees "paid by you at the centre, never collected by us".
3. Per-card document expectation as a count plus a "common mistake" teaser that hands off to a person (flows serve, not inform).

Replace the dated promise with a dated fact: "Last checked Thu 7 Oct 2026, 14:10 by Ayla", beside the official free link.

## Deliverable 2: market home layout spec

Mobile-first at 375px; desktop at 1280px (12 columns, 1200px content). Tokens and composition per the LOCKED identity: dark `--bp-hero-bg` hero and footer, Fraunces headings with one T1 gold-stroke phrase, frosted panels, white body alternating `--bp-paper-tint`. Every WhatsApp CTA is `wa.me/{Market::whatsapp()}?text=` ending `(Ref: BP-XXXXX) [{CODE}]`.

Global states. `enabled=false`: 404. `indexable=false`: same page plus `X-Robots-Tag: noindex`, no hreflang, staging ribbon for Filament users. Copy guards: no em-dashes, no "guaranteed", "fast-track", "priority", "early appointment", "package", no "approved" until in hand, no dates for gated operators.

### S0 Chrome (sticky, 56px mobile / 72px desktop)
Logo `bp-logo-v2-tealgold.svg`, market switcher (flag + `label`, cookie `bp_market`), WhatsApp ghost button, menu. Copy: `team_label`, never "UK Team". Beats: Atlys IP redirect; manual switch only.

### S1 Hero (dark, 720 / 640)
The whole offer in one screen. Gold tag "Schengen visa from {label}"; h1 from `positioning` with one stroked phrase (ZA: "actually answers"); three-sentence lead; primary CTA "Talk to {consultant} on WhatsApp" (teal); secondary "See fees and dates" (ghost, anchors S2 or S5); trust line with gold dots "Companies House 17331903 · ICO ZC197159 · {data_law}". Right on desktop, below on mobile: frosted four-step panel "We watch the calendar / You book in your own name / We prepare your file / You attend and collect". Data: `positioning`, `label`, `data_law`, `consultant_name` (proposed key). Beats: "Guaranteed On-Time Delivery" with a headline promising only what we control.

### S2 Fee cards (dark continues, 420 / 220)
Price before any click. Three frosted cards: "Total {symbol}{price_total}", "To start {symbol}{price_upfront}", "After your appointment is booked {symbol}{price_remainder}"; two small lines: "Government fee (EUR 90 adult) and the visa-centre fee are paid by you at the centre. We never collect them." and "You may apply directly on the official site at no service cost." (links to S5). States: any price null, section hidden. Copy: Fraunces tabular numerals, no decimals. Beats: iVisa "From $399.99" with embassy fee "paid separately"; Atlys's blended "Fees".

### S3 Destination picker (white, 560 / 520)
One country in under five seconds. h2 "Where are you going?"; search over exactly 29 names plus aliases; pill row "Most asked from {label}" (six, `top_destinations`, proposed key); grid of 29 compact cards: flag, name, operator word only when the profile is live ("via VFS Global Sandton"), pill from `MarketBoard::stateFor`, "{n} documents, 1 common mistake"; link to `/{market}/schengen-visa/{slug}` when live, else WhatsApp with "[{CODE}] {country}". Below: "Two or more countries? Which consulate is mine" disclosure, two inputs (most nights, first entry), the rule in plain words, CTA "Confirm my routing with {consultant}". Explanation only; routing stays human. States: no live profiles, all cards go to WhatsApp; no board, no pills. Copy: never "No Visa Required", never a date on a card. Beats: Atlys 120+ grid; nobody offers main-destination help.

### S4 Qualification strip (tint, 400 / 240)
Self-sort before messaging. h2 "Who this is for in {label}"; horizontal scroll (mobile) or 4-up grid of `MarketQualification::tiles()` with green/amber/red pills; US and CA lead with the ETIAS split sentence from SP3 section 4.7. Each tile ends "Tell {consultant} your status". Data: tiles in code (ZA 4, AE 4, US 8, CA 8). Copy: red tiles read "usually refused by the consulate, ask us before paying", never "not eligible". Beats: Atlys "for Indians" fallback H1 and quotes that change after residence is set.

### S5 Availability board, snapshot only (white, 520 / 420)
Who checked, when, and where the free calendar is. h2 "Appointment dates seen this week"; cards per row on mobile, the locked seven-column table on desktop (Destination | Operator | Centre | Booking mode | Earliest date seen | Last checked | Official free booking link); pills per `stateFor`; per-operator lines from `board_copy`; the SP4 global honesty line verbatim; footer "Destinations not yet on this board: we'll check for you on WhatsApp". Home shows the six "most asked" rows plus a link to the hub. Data: `MarketBoard::isLive`, `rows()` keys `state_label, earliest, last_checked, checked_by, booking_url`. States: not live, the SP1 "we will check for you" block; `booking_url` null, no link; `checked_by` null, timestamp only. Copy: dates "Thu 14 Nov 2026"; links "Book free on the official site". Beats: Atlys "Guaranteed Visa On" stamps; Visa Catcher's bot stamp with no human name.

### S6 What you pay, itemised (tint, 480 / 300)
Three-row table: "Our fee {total} ({upfront} now, {remainder} when your appointment is booked)", "Government fee EUR 90 adult, EUR 45 child 6 to 12, free under 6, paid at the centre", "Visa-centre fee, set by the operator, paid at the centre"; one-sentence refund rule; the "apply directly" line. States: price null, first row reads "Our fee for {label} is being finalised"; government rows stay. Beats: Atlys "£0 for now"; iVisa Denial Protection add-on.

### S7 What we do / what we don't (white, 520 / 320)
The VisaD-style block G1 names; two columns, stacked on mobile. Do: watch the calendar daily; check every form line; build the itinerary from your real bookings; tell you if your case is weak before the balance; answer on WhatsApp in stated hours. Don't: book in our name or hold your official login; sell or hold appointments; promise a date or a decision; make dummy bookings; bundle insurance; collect government or centre fees. Beats: Atlys dummy-booking refusals (Trustpilot, 4 Jun 2026).

### S8 Named consultant (tint, 360 / 260)
The human G1 pairs with the board. Real photo with consent, first name, "Your consultant for {label}", `support_hours`, WhatsApp primary CTA, phone only when `phone` set. No photo: initials tile, never stock. Copy: "A person reads every message. No bot." Beats: Atlys BOLO/Tars AI; iVisa chatbot refund flow.

### S9 Trip ideas, enquiry only (white, 420 / 300)
A trip without a bundled holiday. h2 "Trip ideas from {label}"; three cards per the G4 anatomy (country, nights, cities, hotel tier, named ground transport, "visa preparation included", "flights not included; you book, we match the itinerary"); "from {symbol}{n} per person, indicative" only when a price exists; CTA "Ask about this trip"; the G4 compliance strip. Data: SP2 catalogue by market; until then one card "Trip ideas arrive soon; ask {consultant}". Copy: "trip" or "trip idea" only. Gate: PTR memo before any combined price. Beats: Musafir "guaranteed appointment" tours; Tourloom hidden cards.

### S10 Trust strip (dark band, 200 / 120)
`partials.market-trust-strip` over the locked `disclaimer-strip` (dark variant, wrap true): "Registered in England and Wales, Companies House 17331903", "ICO ZC197159", {data_law} statement, "We never sell appointments", "Look us up" link, review link only when `review_url` exists. Beats: Atlys (no UK number, no ICO); iVisa "UK-certified" with no register number.

### S11 FAQ (white, 640 / 480)
Lead questions before paying, minus the deliverable. Accordion of 8 to 10 with FAQPage JSON-LD mirroring visible text: "Is it possible in X days?", "Why pay to start if I can book the slot myself?", "Can you guarantee it?" ("No one honestly can; the consulate decides"), "Which country for two trips?", "Does express make the consulate faster?" ("No, it speeds our handling only"). Copy: no document lists in answers. Beats: Atlys FAQ "No Visa Slots Available Right Now" that sells alerts.

### S12 Footer (dark, 520 / 320)
`lp-footer` via `market_url()`, "Serving applicants in {label}", legal links to `.co.uk`, Organization JSON-LD with Companies House and ICO identifiers, hreflang partial.

### Sticky mobile CTA (56px, after S1 leaves view)
"WhatsApp {consultant}" teal; text carries the last tapped destination or tile. Desktop uses S8.

## Section order and mobile heights (375px)

| # | Section | Surface | Height | Swaps or hides |
|---|---|---|---|---|
| S0 | Chrome | dark | 56 sticky | never |
| S1 | Hero + how it works | dark | 720 | never |
| S2 | Fee cards | dark | 420 | hidden: any price null |
| S3 | Destination picker | white | 560 | never |
| S4 | Qualification strip | tint | 400 | never |
| S5 | Board / check-for-you | white | 520 / 280 | swaps: `isLive` false |
| S6 | Itemised fees | tint | 480 | row swap: price null |
| S7 | Do / don't | white | 520 | never |
| S8 | Named consultant | tint | 360 | never |
| S9 | Trip ideas | white | 420 / 200 | swaps: no catalogue |
| S10 | Trust strip | dark | 200 | never |
| S11 | FAQ | white | 640 | never |
| S12 | Footer | dark | 520 | never |

About 5,800px, seven mobile screens; price and board inside the first two.

## Interactions at launch

Fully functional with only the market enabled:
- S3 search: client-side over 29 names and aliases, keyboard navigable; no-result state "Not a Schengen country; ask {consultant}".
- S3 pills filter the grid and set hash `#dest=france` so shared links land filtered.
- S3 consulate helper: two inputs, instant rule text, WhatsApp handoff.
- Every CTA: `wa.me` deep link with `(Ref: BP-XXXXX) [{CODE}]`, gclid/UTM capture, CRM beacon with `market`.
- S5 board clicks fire a Clarity event (SP7).
- S11 accordion: native `details/summary`.
- Sticky CTA: IntersectionObserver on S1; 48px targets throughout.
- Market switcher: cookie `bp_market`, suggestion banner, never a redirect.
- `@supports not (backdrop-filter)` fallback on every frosted panel.

Deferred, each behind its gate:
- Portal login / "My case": `UKV_PORTAL_ENABLED` (proposed, default off); FAQ points to WhatsApp until then.
- Local-currency payment: `UKV_MARKET_{CODE}_CHECKOUT` (proposed, SP5) plus Stripe live mode (prod is test mode today); no "Pay" button in S2.
- Destination pages behind S3 cards: per-row `MarketDestinationProfile` verified and published (SP3).
- Board rows: `MarketBoard::isLive($market)` after the first logged snapshot (SP4); `UKV_SLOTS_DYNAMIC` stays off on `.com`.
- Trip cards: SP2 catalogue plus the PTR memo per market.
- Indexing: `UKV_MARKET_{CODE}_INDEX`; hreflang and `sitemap-intl` follow it.
- Funnel pages (`UKV_APPLY_ENABLED`, `UKV_TRACK_ENABLED` family): off on `.com` until compliance memos clear.

Owner decisions: new config keys `consultant_name`, `top_destinations`, `review_url`; S5 on the home as six rows or a link to the hub board.

## Build gate added 2026-10-07 (ui-ux-pro-max critical and high rules)

The home page is not done until every line below passes on 375px and 1280px, with reduced motion on and off.

1. Contrast: every text pair at or above 4.5:1, large text 3:1; use the measured tokens in `2026-10-07-com-visual-identity-LOCKED.md` only, no raw hex in views.
2. Touch targets 48px minimum with 8px gaps: CTAs, destination cards, pills used as filters, FAQ summaries, sticky CTA, footer links.
3. Focus: visible 3px ring (`--bp-focus`) on every interactive element; never remove outlines; tab order equals visual order; skip link to the main region as the first focusable element.
4. Headings: one h1 (hero), sequential h2 per section S3 to S11, h3 inside panels; no level skipped.
5. Pills and board states: colour never the only signal; each pill carries its text label and a dot; board rows carry `aria-label` with destination and state; table has a caption and `<th scope>`.
6. Motion: glow and glass are static; any entrance animation 150 to 300ms, transform and opacity only, disabled under `prefers-reduced-motion`; no auto-rotating content anywhere.
7. Layout: no horizontal scroll at 375px; body text 17px minimum; 45 to 75 characters per line; `min-height: 100dvh` not `100vh`; sticky chrome and sticky mobile CTA reserve padding so no content hides behind them; sticky CTA appears only after S1 leaves view.
8. Forms (destination search, consulate helper): visible labels, helper text, inline validation on blur, errors below the field with a recovery path, semantic input types, 44px input height, autosave of entered destination into the WhatsApp deep link.
9. Performance: fonts with `display=swap` and preloaded display weight only; logo as optimised SVG or WebP with width and height set; below-fold images lazy; CLS under 0.1 with reserved space for the board and FAQ.
10. Icons: one stroke set (Lucide, 1.5px), SVG only, never emoji; icon-only buttons carry `aria-label`.
11. Navigation: market switcher and primary nav identical on every `.com` page; current market highlighted; every section reachable by URL fragment; back restores scroll.
12. Copy guards run in tests: no em-dash, no "guaranteed", "fast-track", "priority", "early appointment", no "package"; prices only from config; no "Get a quote".
