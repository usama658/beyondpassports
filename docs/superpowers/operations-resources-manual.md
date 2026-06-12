# Operations resources manual — accounts, appointments, day-to-day, management

The real-world layer: what to sign up for, how to actually get appointments, the daily rhythm, and how to run the
team. Pairs with the staff manual (using the system) + runbook/recipes (the process). The website + production line
are built; this is how you operate the business around them.

---

## 1. Accounts & tools you need

Priority: **P0** = needed to launch · **P1** = needed soon after · **P2** = scale/nice-to-have.

| Tool | Purpose | Where to sign up | Free / Paid | Priority |
|---|---|---|---|---|
| **Domain registrar** | your web address | Namecheap / GoDaddy / Cloudflare | paid (~£10/yr) | P0 |
| **Web hosting** | run the WordPress site | SiteGround / Cloudways / Hostinger (managed WP) | paid (~£5–25/mo) | P0 |
| **Stripe** | take payments | stripe.com → live keys | free + % per txn | P0 |
| **Business email + SMTP** | send the lifecycle emails reliably | Google Workspace / Microsoft 365 + an SMTP relay (Brevo/SendGrid/Postmark) | paid (small) | P0 |
| **Business phone number** | inbound calls (main channel) | a VoIP line (e.g. CircleLoop / Aircall / a local number) | paid | P0 |
| **WhatsApp Business** | the other main channel | WhatsApp Business app (free) now; WhatsApp Business **API** (via Twilio/360dialog) later for automation | free → paid | P0 → P2 |
| **HubSpot** | CRM (already integrated) | hubspot.com (free tier) — **rotate the exposed token** | free tier | P0 |
| **Google Workspace (Drive + Sheets)** | document archive + master log | workspace.google.com | paid (small) | P1 |
| **Zapier** | Drive/Sheet auto-feed (already wired) | zapier.com (free tier) | free tier | P1 |
| **Government / visa-centre portals** | submit applications | per destination: official eVisa/ETA portals; **VFS Global** + **TLScontact** accounts for appointment visas | free accounts | P0 (per first destinations) |
| **Courier account** | tracked + insured passport return | Royal Mail Special Delivery / DHL / FedEx business account | paid per use | P1 |
| **PayPoint info** | IDP guidance (customer collects) | no account — reference only | n/a | P1 |
| **Anthropic API key** | AI doc review + assist | console.anthropic.com | paid (pennies/order) | P1 |
| **Analytics** | GA4 + Microsoft Clarity (already wired) | already set | free | P0 |
| **Accounting** | invoicing, VAT, books | FreeAgent / Xero / QuickBooks | paid | P1 |
| **Business banking** | receive payouts | Tide / Starling / a business account | free/paid | P0 |
| **Insurance** | professional indemnity (handling passports/data) | a UK broker | paid | P1 |
| **ICO registration** | data-protection (you process passport data) | ico.org.uk | ~£40–60/yr | P0 |

**Minimum to go live (P0):** domain + host + Stripe live + SMTP + phone + WhatsApp + HubSpot (rotated) + the official portals for your launch destinations + business bank + ICO registration.

---

## 2. Appointment acquisition — getting slots before they fill

For appointment-based visas (VFS/TLS/embassy), slots are the bottleneck. **Legitimate** tactics only (bots that
break a portal's terms risk account bans):

- **Apply early.** Start the appointment search the moment docs are ready — don't wait for the decision window.
- **Create the centre accounts in advance** (VFS Global, TLScontact) so you can book the instant a slot appears.
- **Check at slot-release times.** Centres release slots in batches (often early morning, local time, on weekdays). Check daily at those windows.
- **Be flexible on date + location.** Offer the customer the earliest slot at any reasonable centre/city — flexibility wins slots.
- **Use official premium/priority services.** Where the centre sells a prime-time / fast-track / premium-lounge slot, offer it as a paid add-on (the system records it). This is the sanctioned fast path.
- **Hold a checklist ready** so a found slot is booked + confirmed in minutes (the appointment pack auto-generates).
- **Track availability per centre** in the supply-chain registry (notes field) so you learn each centre's pattern.
- **Set the customer's expectations** up front: slots vary; we book the earliest legitimately available. Never promise a specific date.

> Do NOT use scraping/auto-booking bots against centre portals — most violate the terms and get the account
> suspended, losing access for ALL your customers. The edge is *preparedness + flexibility + premium options*, not automation that breaks rules.

---

## 3. Day-to-day operations (the rhythm)

**Every morning (start of day):**
1. Open the **Production Line** board + your **My digest** widget.
2. Clear **SLA breaches** and **due-today** first.
3. Check **new orders** → call each within ~1 working hour.
4. Run the **appointment slot check** for any order awaiting a slot.

**Through the day:**
- Chase outstanding documents (system auto-chases at 24h; you follow up).
- Run **doc review + QA sign-off** on complete orders; submit those that pass the gate.
- Answer calls/WhatsApp (primary channels); log a journey note after each contact.
- Action any **barriers** (portal outage, RFE, etc.) + send proactive updates.

**End of day:**
- Update statuses; ensure every owned order has a clear next-action + due date.
- Note anything blocked for tomorrow.

**Weekly:**
- Review **Reports** (revenue, counts by status) + **Success intelligence** (rejection rate, causes).
- Act on **Improvement suggestions**; tighten any destination checklist that's causing refusals.
- Run **Stripe reconcile**; resolve unmatched charges.

**Monthly:**
- Revenue + conversion review; update destination data/fees vs gov.uk; review **Pre-launch/readiness** items still red; renew/check accounts (domain, insurance, ICO).

---

## 4. Management

**Roles (scale as you grow):**
- **Owner/agent** — runs orders end-to-end (call, docs, QA, submit, deliver). Each order has one assigned owner.
- **Lead/ops** — approves refunds, handles escalations (near-travel, refusals, premium decisions), dispatch + courier.
- **Admin/finance** — reconciliation, accounting, account renewals.
(Solo at first = you wear all hats; the system's owner field + digest keep it organised.)

**Ownership + accountability:** every order is assigned (round-robin or by destination expertise); the **owner daily
digest** is each person's worklist; **SLA breaches auto-escalate** to the owner.

**Capacity planning:** track open orders per owner; an agent can typically run a set number of active applications
— when the board column gets deep, add capacity or pause intake.

**KPIs to watch (on the dashboards):** open orders, revenue MTD, **SLA breach count**, **success rate** + **top
rejection causes**, avg processing days, high-risk count, unmatched charges.

**Escalation triggers:** near travel date with no decision · refusal · refund request · passport-validity short ·
no appointment slots · portal outage (destination-wide) · any high-risk flag.

---

## 5. Setup / launch sequence (do in this order)
1. **P0 accounts:** domain → host → business bank + ICO → Stripe live → SMTP → phone + WhatsApp → rotate HubSpot token.
2. **Migrate** the local site to the host (#17) + run **Pre-launch readiness** (Tools) until all green.
3. **Activate** the built integrations (#66): paste real numbers, Zapier URL, Anthropic key, Stripe live keys, SMTP.
4. **Per launch destination:** create the official portal / VFS / TLS accounts; populate **required-docs + validity** + **supply-chain nodes**.
5. **Finish the web pages** (homepage + money/apply/track) in Elementor.
6. **Soft launch:** run a few real orders end-to-end; verify the line + comms; then scale marketing.
