# Deep dive: appointment / slot-monitoring service lane (G1), 2026-10-06

Agent: general-purpose, live fetches 2026-10-06. Reddit blocked; sentiment from Trustpilot, Teamblind, Change.org, Kyiv Post, Gulf News, operator fraud pages. Part of the 2026-10-06 competition-led service deep dives. Items marked unverified were not directly confirmed.

## 1. Mechanics

### Visard (visard.io, Visard LTD, UK)
- Signup: Telegram only (@vissard_bot): select service type, destination, application country, visa type; wait; appointment booked. No web dashboard, no WhatsApp.
- Data: alerts tier "need no passport or personal data"; auto-book: details once via bot; delivers "the official VFS/TLS booking confirmation, identical to one you'd get booking yourself".
- Polling claim: "VFS Global, TLScontact, and BLS International checked 28,800 times per day" (every 3 seconds); alert delivery "under 1 second".
- Auto-book: "fills the booking form and confirms the appointment in seconds"; payment link; T&C s.26 pay within 24h or appointment cancelled.
- Alerts tier: user books themselves "within 1 minute".
- Coverage: UK, Ireland, UAE, USA, India, Turkey, Morocco. "77 corridors monitored", auto-book in 30 (UK 17-19, counts conflict, unverified; Ireland 6; USA 1 = Portugal). UK: notifications-only Spain, Italy, Switzerland; "Not currently covered: France, Germany". UAE: 12 destinations alerts only, auto-book "not offered" due to "visa centre system restrictions". USA: 4 destinations; "technical restrictions on other US VFS systems prevent automation".
- Gaps: two biggest UK corridors (France TLS, Germany TLS) absent; no public board (tracker URL is marketing).
- Claim: "Most users secure an appointment within 4-7 days"; "30% of slots are for next week, 40% within the current month".

### Visa Catcher (visacatcher.bot)
- Free tier: Telegram per-country channels, "no account and no personal data". Paid: collects what the VAC asks; T&C 5.5 may create dedicated email + VAC portal accounts, credentials handed over as result.
- Polling: "every few seconds", "24/7 Route Monitoring".
- Auto-book: books when matching slot appears; prepays VAC fee only if mandatory online.
- Coverage: 27 cities incl. London/Manchester/Edinburgh, Ireland, UAE, USA, Canada (Toronto, Vancouver, Montreal), Turkey; "383 routes across 27 application centres". London board: 28 destinations, 9 paid-only (France, Spain, Bulgaria, Hungary, Ireland, NZ, Singapore, South Africa, Ukraine). Operators VFS, TLScontact, BLS, Almaviva.
- Gaps: France-from-London page is about an airport-transit visa via VFS (wrong operator); route data quality uneven. No SA application market.

### VisaD (panelx.tech; hello@visad.co.uk)
- Web form (destination, name/email/phone, location, appointment type, citizenship, date range). "Submitting this does not book an appointment."
- "continuous checks of the official booking calendars, day and night, for 30 days"; WhatsApp alert with direct link to official page. No auto-booking: "We don't book. You take the slot yourself on the official portal" using own account.
- 29 Schengen countries; VFS/TLS/BLS; "no affiliation".
- visad.co.uk (same email) is an e-visa product ("£99 per applicant" Singapore; "Full refund if no appointment"); relationship unverified.

### Atlys (atlys.com/appointments/schengen)
- Filters (from, destination, city, purpose, travellers) -> board of earliest slots -> "Book this appointment" -> add travellers, upload docs, Atlys handles final booking; "Slots reserved via Atlys are held during your application".
- "We poll every official Schengen visa portal multiple times per minute. New slots usually land within 60 seconds"; push notifications.
- All Schengen; India board live; UK/UAE/US exist; Canada none. No human; appointment fee not shown (bundled, unverified).

### Open-source / Telegram bots (mechanics behind the paid bots)
- North-web-dev/vfs-monitor: polls VFS `CheckIsSlotAvailable`; Capsolver for Cloudflare Turnstile; caches cf_clearance; account pool rotated; residential proxies pinned to centre regions. Error taxonomy: "Per-account limits (429001): triggered by email, not IP, use a different account"; "Per-IP limits (429201): transient Cloudflare blocks"; "Soft-blocks: empty results when polling five categories simultaneously". Matches BP's own sweep findings.
- kaven0667/VFS-appointment-bot-Telegram: Camoufox, credential rotation, Turnstile bypass, OTP via Gmail IMAP, SOCKS5; "Rate limit -> next account, 5 minute cooldown".
- bartug/visa-appointment-tracker (honest end): "No captcha solving, no auto-booking"; min 900s interval; "VFS terms don't permit automated access"; "Frequent requests lead to IP blocking and account suspension".
- Free channels: @UKVFSBot, @canadausavfsbot, @VFSVisaExplorerBot; VisasBot (1,929 Trustpilot reviews). VisaBot/xVisa (visabot.eu) "From £35/month", auto-booking "From £100/applicant", 1500+ routes. MyVisaPing "£13.99 one payment", monitoring only.

## 2. Pricing mechanics
| Service | Alerts | Auto-book | When charged |
|---|---|---|---|
| Visard | £35 single / £65 all (31 days); €40/€50; $40/$60; AED 200/350; ₹2,500/4,600 | £100 first + £50 each additional | Alerts upfront, no auto-renewal; auto-book fee "charged only after your appointment is confirmed"; VAC fee upfront, "refunded if no booking secured" |
| Visa Catcher | Free channels; "Alerts £30/mo" badge; "Premium from £30" | "from £65" (board badge; /pricing 404); France "Quote on request" | Alerts in advance; auto-book: VAC prepayment at order, Provider Fee after booking, due within 5 days |
| VisaD | Fee "the amount we confirm to you before you pay"; 30-day period; amount unpublished | none | Upfront |
| Atlys | Bundled (undisclosed) | Included | Upfront |
| VisaBot | From £35/month non-recurring, all travellers | From £100/applicant | Alerts upfront |
| MyVisaPing | £13.99 once | none | Upfront |

Refund/guarantee wording:
- Visard: "We request payment only after we send you the confirmed booking. No appointment = no payment." Alerts: refund/extension only "if you can show evidence of a missed slot". T&C: notifications "no refunds irrespective of changes in plans"; auto-book fee post-booking non-refundable; liability capped to one month's payments.
- Visa Catcher: 6.1.7 full refund if no matching notification in 30 days (or extension/credit); 6.1.9 free cancel within 5 days, 20% after until first notification; 6.2.7 no Provider Fee if no booking; "No refund once your first matching alert has been sent, that is the result you bought"; no refund if booked with incorrect details or appointment missed; 14-day withdrawal pro rata; 9.3 reimburses VAC fees when cancellation "attributed to automated use".
- VisaD: "If no matching slot is found and no alert is sent during your monitoring period, we refund your fee in full." Once alerted, non-refundable "including where you did not see the alert in time".
- Atlys: "will not refund the Visa fees or the Atlys fees for any reason whatsoever, except ... 'Atlys Protect'"; slot display "indicative, best effort"; liability cap "amount paid or 100 USD".
- Per-applicant: Visard alerts cover whole family; auto-book per applicant. Visa Catcher per Order.

## 3. Honesty / compliance language
- Visa Catcher T&C 5.1: "We do not and cannot guarantee that any slot will become available ... or that a slot notified will still be available when a booking is attempted." 5.2: "An appointment is not a visa ... we cannot accelerate the processing." 5.3: VACs "may cancel bookings, or block booking accounts attributed to automated use". Board: "Book immediately when you see an available slot. They disappear within minutes."
- VisaD: "No appointment is booked by this form and no appointment is guaranteed." "anyone promising otherwise is misleading you." "We cannot create availability and we do not guarantee that a slot will appear, that you will secure one, or that you will obtain any appointment, visa or document."
- MyVisaPing: "cannot replace registration, influence allocation order or create capacity."
- Visard (weakest): headline "Book a Schengen Visa Appointment in 1 Week"; no mention of VFS/TLS rate limits or bans on site; only in Trustpilot replies ("IP blocks from TLS security measures").
- Operators (use verbatim in BP copy): VFS "Appointments are free of cost and can be booked only on www.vfsglobal.com."; "Availability of visa appointment slots are at the sole discretion of the governments"; "VFS Global does not work in association with any third-party entities."; "There is no fast-track service for Schengen visas." (Gulf News). TLScontact Edinburgh: "Please do not purchase appointments via a representative/intermediary: this is a fraudulent practice" and "unable to guarantee that any appointments procured via these third parties will be honoured". VFS to Kyiv Post: "robust security measures ... to protect our appointment booking system from automated or bot-driven activity." TLScontact France UK (from 1 Aug 2026, London + Manchester): applicants "automatically allocated an appointment", email offer, "12 hours to pay the service fee and confirm", purpose "to protect visa applicants from fraudulent and costly third-party agencies" (search summary; page 403, partly unverified).

## 4. UX patterns
- Visa Catcher board (best in class, free, crawlable): Country | Visa Type | Availability badge | Earliest Date | Last Checked | Actions. States: "Appointments Available" + "3 dates available" + date; "Waitlist Open"; "No Current Availability" + "Last seen an hour ago / 7 months ago"; "Paid Service" badge with price. Tabs All/Available/Waitlist/Unavailable/Paid; sort; table/card toggle. Hub cards per city: "6 OPEN", route count, earliest date, "checked 24s ago". "Last seen X ago" on empty rows = strongest honesty device observed.
- Atlys board: filters; stat cards (countries with slots, open cities, earliest slot); "Live" badge; "No Slots" explained as "fully booked or portals down"; freshness timestamp; only centres fitting group size.
- Visard: no board, pure Telegram. VisaD: form -> WhatsApp alerts with deep link.
- Channels: Telegram dominates bots; WhatsApp = VisaD; email/SMS = MyVisaPing, VisaBot. Nobody pairs a board with a named human.

## 5. User pain (quoted)
- Visard Trustpilot: "by the time you click the link the slot is gone" (Mar 2024); "it takes me 5 to 10 seconds to log in and check the slots, and they are always unavailable" (Mar 2024); "within the same second I receive notification ... still says no appointment available" (Apr 2024); "your profile is locked so no meaning of getting notifications" (Visard: "TLS IP security blocks, outside their control"); "notifies available group slots but when you check there is none" (Jun 2025); "did not even get one notification even some slots were open" (Jul 2026, refunded); "Its 20 days they haven't booked the appointment and the application is removed by TLS" (Feb 2026); "does not provide refunds in the middle of a subscription period" (Sep 2025); "customer care for the past 10 days no response" (Mar 2025).
- VisasBot: "notification arrives too late; the appointment is already gone" (Apr 2025); "hasn't been working for almost 1.5 months" (Sep 2025); "got zero guidance after payment" (Jul 2025).
- Visa Catcher: "Got threatened i wont get the appointment if i dont remove my 3 star review" (Jul 2026; denied by company).
- VFS Trustpilot: "slots appear to be taken by bots and intermediaries ... '429: Failed to get response'" (Apr 2026); "tells me I've had too many attempts"; "It always shows not available."
- Change.org: "blockading users with 403 Forbidden errors ... pay hundreds of pounds to 'black market' services."
- Kyiv Post: brokers "use automated bots to detect newly released slots"; "UKVFSBot and Visard.io operate openly on Telegram".
- findmyvisa.co.uk: "several consulates void bot-booked appointments" (which, unverified).
- Teamblind: Telegram groups "spam"; SF Italy thread "no slots for 3 months", advice "get visa for France, train to Switzerland" (users coaching main-destination-rule violations).
- schengenalert.com: "Skip the bots and 'guaranteed appointment' agents ... automated logins are exactly the behavior that gets accounts flagged."

## 6. Where the human is missing
1. "Am I ready to book?" Slots vanish in 5-30 seconds; worthless without insurance, itinerary, form, cover letter ready. Bots alert; nobody checks readiness.
2. "Which consulate / country must I apply to?" Forum advice breaks the main-destination rule; Visard FAQ silent.
3. "Which centre / city?" Operator routing differs per destination; Visa Catcher France page is the wrong operator; Visard omits France/Germany.
4. "Is this date right for my travel date?" 15-day processing, 6-month window, 12-hour TLS confirmation, BLS paid tiers: nobody times the appointment to the trip.
5. "What do I do when there are no slots?" Waitlist vs queue vs calendar; TLS France UK is allocation-based so "refresh faster" is wrong advice.
6. "Why is my account locked?" (Visard: outside their control.)
7. "I booked wrong details / category" (Visa Catcher: no refund).
8. "Will the appointment be honoured?" (TLScontact may refuse third-party-procured.)
9. "Appointment is 3 weeks out, do I still travel? Switch destination?" Judgement calls.

## 7. Design recommendation for G1
**Name options**: "Watch & Prepare"; "Appointment Watch"; "Slot Watch + Consultant"; "Monitored Appointment Service". Avoid "Fast-track", "Priority appointment", "Guaranteed slot", "Early appointment".

**Included**: named consultant on WhatsApp (first name + photo), honest reply SLA; per-destination routing (operator, centre, booking mode, main-destination check, timing vs travel); readiness pack before any booking attempt (existing £130 inclusions); monitoring = human-paced checks on BP's own accounts + lawful alert feeds, client messaged on WhatsApp, client books on own account (or agent-assisted where operator permits), never automated logins on client accounts; post-booking: appointment-day checklist, 12/24h confirmation handling for allocation systems, what-if-no-slot plan.

**Board per market** (reuse hero-urgency-config enums): Destination | Operator | Centre(s) | Booking mode (Calendar / Waitlist open / Allocation queue / Embassy direct) | Earliest date seen (exact when snapshot <7 days, else band) | Last checked (timestamp + "by {name}") | Official booking link ("Appointments are free on the official site"). States: Available / Filling / Limited / Waitlist / Allocation queue / No dates seen (+ "last seen DD Mon") / Portal unavailable / "We'll check for you" (stale >7 days). Copy: "Checked by a person, not a bot. Dates move within minutes; a date shown here can be gone before you log in. We never hold or sell appointments." Rule: no row shows a date without a timestamped real snapshot. FLAG: the simulated 70/week dynamic pool (appointment-slots-dynamic) contradicts G1's honesty wedge; switch .com boards to snapshot-only, keep dynamic OFF on .com.

**WhatsApp flow**: 1 lead gives destination, dates, city, group size, residence status -> 2 routing verdict + board reading with timestamp -> 3 readiness check (doc list in chat, not on page) -> 4 £40 to start, monitoring window opens (30 days, extendable) -> 5 alert template "{Country} {centre}: dates seen for {DD Mon} at {HH:MM}. Log in now on {official link}. I'm online if you hit an error." + allocation-queue variant ("Offer email expected; you have 12 hours to pay; message me when it lands") -> 6 client forwards official confirmation -> £90 due.

**Fee wording (Visard-style)**: "£130 in total. £40 to start, which covers your consultant, routing and document preparation. The remaining £90 is due only after your appointment is confirmed on the official visa centre site. No confirmed appointment, nothing more to pay." + "Visa centre and consulate fees are paid by you directly to the official provider and are not included." + "£130 per applicant; one consultant covers the whole group."

**Refund rules (draft, owner decision)**: £90 never charged unless confirmed. £40 full refund if routing verdict + prep not started within 2 working days; partial/no refund once prep delivered (stated on page). Outcome refund (VisaD): no appointment within window -> extend 30 days free, or refund £40 less delivered add-ons. Client cancellation after confirmation: no £90 refund, one free rebooking attempt. 14-day Consumer Contracts Regulations withdrawal pro rata.

**"What we don't do" (draft)**: "We do not sell, hold or block-book appointments. Appointments are free and are booked only on the official VFS Global, TLScontact, BLS or consulate site, in your name. We do not run bots on your account or share your login. We cannot create availability, promise a date, or speed up the consulate's decision. There is no fast-track for Schengen visas. We are not affiliated with VFS Global, TLScontact, BLS International, any embassy, consulate or government. What we do: watch availability for your route, tell you the moment it moves, make sure every document is ready before it does, and stay on WhatsApp while you book."

**5 compliance red lines**: (1) no guaranteed/early/priority/fast-track/dated promises anywhere incl. WhatsApp templates and ads; (2) no automated access to client accounts, no credential sharing, human-paced checks on BP accounts under one-check-per-country; (3) no slot holding, reselling, dummy bookings; (4) board shows only timestamped real snapshots, no simulated counts on G1 markets; (5) price + split + "book directly for free on the official site" beside the fee on every money page, refund rule on page, non-affiliation disclaimer in confirmations and alert messages.

**Unverified**: Visard TRY/MAD prices; UK auto-book count; VisaD fee + relationship to visad.co.uk; Visa Catcher "from £65" as list price; Atlys appointment fee; TLScontact news wording; which consulates void bot-booked slots; all Reddit sentiment.

Sources: visard.io (home, faq, uk, uae, usa, uk-alerts, about, terms, blog), visacatcher.bot (home, terms, refunds, free-bot, appointments, london, london/france, subscribe), panelx.tech (home, terms), visad.co.uk, atlys.com (appointments/schengen, en-IN tools, en-US terms, blog), visabot.eu, myvisaping.com, Trustpilot (visard.io, visacatcher.bot, visasbot.com, visa.vfsglobal.com), vfsglobal.com/en/donotfallforfraud, TLScontact Edinburgh scam alert, Gulf News, Kyiv Post 76172, Change.org VFS petition, schengenalert.com, schengenvisasupport.com, findmyvisa.co.uk, torly.ai, Teamblind (2 threads), GitHub (vfs-monitor, visa-appointment-tracker, VFS-appointment-bot-Telegram).
