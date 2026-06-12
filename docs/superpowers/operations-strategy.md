# Operations strategy — my recommendation + why (budget · launch priority · appointments)

This is opinionated. It pairs with the operations-resources-manual (the full account/process detail). The goal:
reach profit fast with the least risk, then expand.

## The core call: phase it

**Phase 1 — online-only (eVisa / ETA / ESTA / eTA). Start here.**
Why: fully digital, no appointment, turnaround in hours–days, you never hold a passport, no courier/insurance/
custody risk. The customer pays the government fee + our service fee; our work is *checking + submitting* — the
highest **margin per hour** in this business and the fastest route to cash. The site already covers ~14 of these.

**Phase 2 — appointment / sticker visas (VFS/TLS/embassy, biometrics). Add later, deliberately.**
Why defer: slot scarcity is a hard bottleneck you don't control; you take **custody of passports** (insurance +
liability + tracked courier cost); cycles are weeks not days; ops overhead per order is much higher. The system
already supports all of it (appointments, premium slots, passport-return tracking, supply-chain registry) — so
turning it on later is a switch, not a rebuild. Do it once Phase 1 is **proven + profitable**, not before.

**Why this order wins:** you validate demand, the funnel, the production line, and your own capacity on the
*easy, high-margin* product — with near-zero fixed cost and no liability — then reinvest the profit into the
harder, higher-ticket appointment segment. Trying to do everything at launch spreads you thin and adds risk
before you've earned a single order.

## A. Costed budget (rough, GBP)

**Startup (one-off, ~£150–350):**
- Domain ~£10/yr · managed WP host setup (often first month) · **ICO data-protection registration ~£40–60/yr** (you process passport data — not optional) · professional-indemnity insurance (first month) · business bank (free) · logo/brand if needed.

**Monthly — LEAN (Phase 1, ~£40–90/mo):**
- Host ~£10–25 · SMTP (Brevo/SendGrid free–£10) · phone/VoIP ~£5–15 · WhatsApp Business app **free** · HubSpot **free tier** · Zapier **free tier** · GA4 + Clarity **free** · Anthropic **pennies/order** · accounting ~£10–20 · insurance ~£10–20.
- **Stripe** = ~1.5%+20p per UK card txn (variable, not fixed).

**Monthly — FULL (Phase 2 / scale, ~£150–400+/mo):**
- Adds: WhatsApp Business **API** (Twilio/360dialog) · paid HubSpot tier · courier account (per-use) · premium-slot/centre fees (pass-through to customer) · paid SMTP volume · more seats/tools.

**My take:** stay on the **lean** stack until revenue clearly justifies each upgrade. Almost everything you need
for Phase 1 is free-tier or a few pounds. Don't buy the WhatsApp API, paid CRM, or courier account until you're
actually doing the work that needs them. Keep fixed costs near-zero so you're profitable from the first orders.

## B. Per-destination launch priority + setup checklist

**Launch-first set (online-only, highest demand + easiest):**
Turkey · Egypt · India · Vietnam · Sri Lanka · Kenya · USA (ESTA) · Canada (eTA). All eVisa/ETA, no appointment.

**Per destination, before you sell it, lock down:**
- [ ] The **official portal** URL + create an operator account there.
- [ ] **Required documents** list (set in Pods `required_docs`).
- [ ] **Passport-validity months** (set in Pods — activates the validity barrier).
- [ ] **Government fee** + your service-fee tiers (already in Pods; verify vs gov.uk).
- [ ] **Typical processing time** (set cautious expectations).
- [ ] Any **special rules** (insurance, onward ticket, arrival card) → the destination's "extras" note.
- [ ] Confirm it's online-only (no appointment) for Phase 1.

**Phase-2 set (appointment/biometric — add later):** Schengen (full visa where applicable), and any sticker-visa
destinations needing VFS/TLS/embassy. For each, additionally set up: the **VFS/TLS account**, the **supply-chain
node** (centre + contact + SLA), a **courier account** for passport return, and confirm your **insurance** covers
passport custody.

**My take:** pick **3–4** Phase-1 destinations to start (not all 14) so your data, copy, and expertise are deep,
not thin. Lead with the ones you understand best + that have steady UK demand (Turkey, Egypt, India are strong).

## C. Appointments — when this actually matters

**For Phase 1: it doesn't.** Online visas have no appointment, so skip the whole appointment apparatus at launch —
don't spend energy on slot tactics you won't use yet.

**For Phase 2, the legitimate playbook (recap from the ops manual):** pre-create centre accounts; check at slot-
release windows (often early-morning weekday batches); be flexible on date/centre; offer official premium/fast-
track slots as a paid add-on; keep a ready-to-book checklist so a found slot is secured in minutes; record each
centre's pattern in the supply-chain registry. **Never** run scraping/auto-booking bots — they breach centre terms
and get the account banned for *all* your customers; the real edge is preparedness + flexibility + premium options.

**My take:** the moment you add your first appointment-visa destination, *that* is when you invest in the slot
routine — and consider a paid premium-slot offering, because customers facing a slot shortage will happily pay for
a faster legitimate path, and it's pure margin.

## Bottom line
Launch lean, online-only, 3–4 destinations, near-zero fixed cost → prove the line + reach profit → then switch on
the (already-built) appointment machinery and expand. The software is done; this sequencing is what de-risks the
*business*.
