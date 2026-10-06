# xVisa home critique and deltas for the .com market home spec

Status: draft for owner review, 2026-10-07. Companion to `2026-10-07-com-home-layout-critique-and-spec.md`. Target: `xvisa.com`.

Sources. Primary: `C:\Users\mumya\Downloads\xVisa _ Get Your Visa On Time.htm` (saved 2026-10-07 01:46, signed in, dark theme on; counters "as of 6 Oct 2026"); visible text, FAQPage JSON-LD and the Trustpilot widget read. Live `xvisa.com` and `www.xvisa.com` fetched 2026-10-07: both failed, live page [unverified]. Companies House 14986978 fetched 2026-10-07: TREVOR PHILIPS LTD, Active, incorporated 7 July 2023, SIC 62090 and 79110. Identity: `xvisa.com` is the rebranded Telegram bot `visabot.eu` (widget and footer link to `uk.trustpilot.com/review/visabot.eu`), not the `xvisa.co.uk` agency in the portal matrix.

Fonts and colours (saved CSS): Geist, Geist Mono, Source Serif 4; blue `#0d7bff`, orange CTA `#fa5d36`, dark `#0b0b0b` `#171b1f`, light `#f8f8f8`; Next.js with Tailwind.

## 1. Home anatomy (saved copy, in order)

1. Disclaimer bar above the nav: "Visa issuance decisions are made solely by the relevant authorities."
2. Nav: How it Works, Reviews, FAQ, Time Estimation, My Applications (login), language, theme. No phone, no WhatsApp.
3. Hero: chip "Trusted by 10,000+ travelers, 4.8 rating on Trustpilot"; H1 "Get your visa on time"; counter "31,303+ Visas processed by now, as of 6 Oct 2026".
4. Form card: Applying from (United Kingdom), Destination, Visa Center, Visa Type as cascading selects; CTA "Check My Options"; chips "Encrypted with AES-256", "95% success rate", "Starting at £100"; link "Find the fastest visa appointment".
5. How it works, four steps: we pick "the destination and visa center with the earliest appointment availability"; upload; "AI powered tool checks your documents"; "AutoBooking system continuously monitors available appointment slots".
6. "What We Do, and What We Don't": five lines each, including "Treat every applicant equally, regardless of what they pay".
7. Trustpilot widget "4.8 / 5 based on 1,044 reviews", labelled "Showing our 3, 4 & 5 star reviews"; then a DIY versus xVisa table.
9. FAQ, eight questions with schema: "95% success rate across all visa types"; cost "set per country... in your own currency during the application process"; "apply to the country you'll spend the most time in"; support "chat widget... within a few minutes during business hours. We do not offer phone or email support"; refund of "the initial service fee if we do not manage to book you an appointment within 14 calendar days".
10. Price Breakdown: "Government fee £30 × 1", "Processing Fee £100, After successful completion", "Total Amount £130", "Payable now £30".
11. Route guides: 104 links across 22 origin markets (UK 21, UAE 10, US 3, Canada 2; no South Africa).
12. Footer: "emergency visa appointment booking"; social links; Legal; disclaimer naming VFS Global, TLScontact, BLS ("on paid plans, complete the booking on your behalf"); "TREVOR PHILIPS LTD · Company no. 14986978" linked to Companies House; VAT GB465562175; registered office; cookie "Reject all".

Support: Intercom chat only per the FAQ; contact-page copy says "email, Telegram or live chat... within 24 hours". No hours, time zone, phone, WhatsApp or named person.

## 2. Critique

### (a) What works
1. Honesty early and often: disclaimer bar, do/don't block, operators named, "consulate decides" in the FAQ. Closest UK page to G1's framing.
2. Price arithmetic on the page: total, "Payable now £30", remainder after completion, per traveller.
3. Refund rule in the FAQ with a 14-day trigger and a delivery definition.
4. Verifiable company line: Companies House link that resolves, VAT, office.
5. Sound skeleton: one H1, ten H2, FAQPage schema, `lang="en"`, cookie "Reject all".

### (b) What fails or misleads
1. "Get your visa on time" and "95% success rate" sit beside a FAQ saying "The decision itself is always made by the consulate". Three trust numbers (10,000+, 31,303+, 1,044) disagree; the widget shows only 3 to 5 star reviews, for visabot.eu.
2. "Government fee £30" is not the government fee. The Schengen consular fee is EUR 90; the page never says what the £30 is.
3. Two price stories: a fixed £130 block versus FAQ "set per country... in your own currency", plus "Starting at £100" in the hero.
4. "Find the fastest visa appointment" invites country shopping, which the same page's FAQ forbids (a refusal risk per the UAE deep dive).
5. The product is a bot: "AutoBooking... continuously monitors" and "complete the booking on your behalf" are what the official-portal pain map says VFS (429 bans), TLScontact and Prenot@mi block.
6. No human: chat "during business hours" with no hours stated, "no phone or email support", while the contact page promises 24 hours.
7. No ICO or data-protection registration; "AES-256" stands in for it.
8. Accessibility: blue `#0d7bff` on white 3.97:1 and white on orange `#fa5d36` 3.14:1 fail AA; 265 uses of 12px `text-xs`; 21 buttons at 36 to 40px.
9. 104 route links for 22 markets on one home: Atlys-style sprawl, without the search.

### (c) Steal and improve; do not copy
Steal: (1) the do/don't block with "treat every applicant equally, whatever they pay", extended with what we never automate; (2) "Payable now / after" arithmetic with the consular fee named correctly and "who you pay" per line; (3) a refund rule with a time trigger and delivery definition, in the FAQ and beside the price.
Do not copy: "on time" headline; unsourced success rate; "fastest appointment" routing; auto-booking on the applicant's behalf; filtered or borrowed review widgets (and chat-only support, a deposit labelled as a government fee, multi-market link dumps).

Closer to us: xVisa, by distance. It says the consulate decides, itemises a split, links its registration and lists what it will not do; it fails where we win (books by script, routes by speed, no human, no hours). Atlys is a marketplace with dated promises; nothing transfers. Our wedge against xVisa: a person checks, you book in your own name, one fee for every destination, a name and hours on the page.

## 3. Deltas to the market home spec

Locked identity and the a11y gate (7.5:1 CTA, 48px targets, 13px minimum) stay as written.

| Section | Delta | xVisa evidence |
|---|---|---|
| S0 | Add a 32px independence line above the chrome via the locked `disclaimer-strip` (dark, wrap false): "Independent consultancy. Visa decisions are made by the consulate." | Disclaimer bar is first on the page; Google Ads Government Documents rule |
| S1 | No change: no counters, no success rate, no "on time" | Three conflicting trust numbers; "95%" beside "consulate decides" |
| S2 | Add a "who you pay" micro-label per card (Us / Us / The visa centre); name "Consular fee EUR 90 (adult)"; never put our start fee under a government label | "Government fee £30 × 1" unexplained |
| S3 | Add under the consulate helper: "We never choose a country by appointment speed; the consulate checks your itinerary." Optional city/province select surfacing the board `centre` when live | "Find the fastest visa appointment"; own FAQ forbids it |
| S4 | No change | No qualification step on xVisa |
| S5 | Add under the global line: "We do not run scripts against the booking sites and never book in our name; you book in yours." Link to the new FAQ item | "AutoBooking... continuously monitors"; "complete the booking on your behalf"; VFS 429 rule |
| S6 | Add "One fee for all 29 destinations from {label}"; make the refund rule time-bound and delivery-defined (owner sets the trigger; lessons L109 conflict stands) | Fixed £130 versus "set per country"; 14-day trigger |
| S7 | Add Do "treat every client the same whatever they pay"; add Don't "run scripts against official booking sites" and "offer paid queue-jumping slots" | xVisa's own lines; its mechanism contradicts them |
| S8 | Publish hours in the market's time zone ("09:00 to 18:00 UK, 10:00 to 19:00 SAST") and one reply promise; list email beside WhatsApp | Chat-only, no hours, contact page says 24 hours |
| S9 | No change | Not present |
| S10 | Add registered office and VAT if registered (owner); link Companies House directly; review link "all reviews, unfiltered", live platform count only | CH link, VAT, office in footer; widget filtered to 3 to 5 stars, other domain |
| S11 | Add "Do you book the appointment for me?" (No, you book in your own name), "How do I reach you and when?", "What is your refund rule?" (same text as S6) | xVisa FAQ covers support and refund; ours lacked both |
| S12 | No change; footer links only live profiles for the current market | 104 links, 22 markets on one page |
| Sticky CTA | No change | None on xVisa |

## 4. Ten customer questions

| Question | Atlys (en-ID, 2026-10-07) | xVisa (saved 2026-10-07) | Our spec |
|---|---|---|---|
| Price before click | Blended "Fees" per card | £130 block, £30 now; hero "Starting at £100" conflicts | Three cards; hidden if null |
| Government fee itemised | No | Mislabelled "£30 government fee" | EUR 90 consular and centre fee named, paid at the centre |
| Who checks dates, when | "Guaranteed Visa On" stamp [unverified in fetch] | Bot "continuously monitors" | Named person, timestamp per row, official free link |
| Human contact, hours | 24/7 claim, AI first | Chat widget, no hours, no phone or email | Named consultant, hours in market time, WhatsApp and email |
| Refund rule visible | Policy page; Terms contradict | FAQ, 14-day trigger | S6 and FAQ, time-bound (owner to set) |
| Registration numbers | None found | Companies House 14986978, VAT; no ICO | Companies House 17331903, ICO ZC197159, data-law line |
| Honest about appointments | Dated promise vs "not binding" Terms | Do/don't block, yet auto-books on your behalf | "We watch, you book in your own name, we prepare" |
| Mobile usability | Long grid, app-first OTP | 36 to 40px buttons, 12px text, 3.97:1 blue | 48px targets, 17px body, 7.5:1 CTA, sticky CTA |
| Trust proof type | Review counts, "Wall of Love" [unverified] | Filtered widget for visabot.eu | Verifiable registrations, named checker, live review count |
| Time to first useful answer | After search and card scroll | After four cascading selects and sign-up | Hero states offer and fee |
