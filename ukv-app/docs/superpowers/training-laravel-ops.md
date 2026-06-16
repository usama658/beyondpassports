# Beyond Passports — Operator Training Manual (Laravel + Filament back-office)

This is the operating manual for the **new** Beyond Passports back-office. The WordPress system is
retired; everything below happens in the Filament admin at **`/admin`**. It is written to be
followed step-by-step at the screen — every page, action, column and field named here is real.

> **Compliance — read first, repeat to every customer.**
> - We are an **independent visa support service. We are NOT a government website** and not affiliated with gov.uk, UKVI, or any embassy.
> - The **service fee and the government fee are separate.** The government fee is the embassy/authority's own charge and is shown separately on every order (the `govt_fee` field).
> - **"Express"/priority speeds OUR handling and paperwork. It does NOT make the government decide faster** and never changes the decision.
> - **We never guarantee approval** or any specific outcome.
> - **IDP (International Driving Permit) is guided self-service** — issued in person by PayPoint. We guide the customer; we do not issue or post it for them.
> - **Never send passport numbers, document scans, or customer PII to AI.** The AI layer is advisory only and is leak-gated (see M5).

---

## Orientation — the admin at a glance

Sign in at `/admin`. The left navigation is grouped:

- **Operations** — Orders, Production Line, Reports, Barriers, Client updates, Appointments, Rejections, Feedback.
- **Commerce** — Discounts, Quotes.
- **Catalogue** — Destinations, Supply nodes.

The **Dashboard** (home) shows the KPI strip (`OrdersStatsOverview` widget) and the
**Orders per week** trend chart (`OrdersPerWeekChart`).

**Roles** (`UserRole`): **Admin** and **Agent** have full write access; **Viewer** is read-only
everywhere (cannot create/edit/delete any record). Roles are enforced by the `AuthorizesByRole`
trait on every resource.

**The pipeline** (`OrderStatus`) runs left to right:

`paid → awaiting_docs → doc_review → submitted → awaiting_decision → delivered → won`

with terminal off-ramps **rejected** and **refunded**. The *closed set* is
**delivered, won, rejected, refunded** — these stop SLA/digest chasing and start the retention clock.

---

## M3 — Run an order end-to-end (task #106)

### 3.1 Find an order
**Operations → Orders.** The table (`OrderResource`) defaults to newest first. Search by
**Ref**, **Customer** (name/email), **Phone**, or **Destination**. Filter by **Status**,
**Eligibility lane**, **Destination**, or the **Flagged for review** toggle. Key columns:
Ref, Customer (email shown beneath), Status badge, **Lane** badge, Tier, Total, Priority, **Risk**, Created.
Click a row to open it, or use the **Edit** screen.

### 3.2 Read the journey / events
Every order carries an append-only event log (`order_events`). It records the opening
"Order created from apply intake" event, every **stage change** ("Status changed: X → Y"),
every **email sent** ("Email sent: <event>"), discount/loyalty notes, and CRM syncs.
This is the audit trail — read it before you act, never edit history.

### 3.3 Advance stages — the gates
Use the **Advance stage** table action (arrow-circle icon) on the order row, or the per-card
**Advance** button on the Production Line. Pick the target status; the move is run through
`OrderService::transition()`, which enforces the gates **in this order** and shows any block as a
red notification:

1. **Pipeline adjacency** — you can only move one step forward (or to a legal terminal branch).
2. **Eligibility gate** — a non-cleared (manual_review/referred) order **cannot pass `paid`** until cleared (see 3.6).
3. **Doc gate (`→ doc_review`)** — at least **one document** must be uploaded.
4. **QA gate (`→ submitted`)** — the sole authority on submission (see 3.4).
5. **Government-ref gate (`→ awaiting_decision` and `→ delivered`)** — a **government reference must be recorded** first (see 3.5).

The off-ramps from `awaiting_decision` are **delivered** or **rejected**; from `delivered` you go
to **won** or **rejected**. (Refunds never go through Advance — use the Refund action, 3.7.)

### 3.4 The QA sign-off (gate to `submitted`)
`OrderService::qaCanSubmit()` blocks submission unless **both**:
- **Documents are complete** — uploaded count ≥ the destination's `required_docs_count` (floor of 1 if none is set), and
- **`qa_signed_off` is recorded** on the order.

If you try to advance to **Submitted** without these, you get
*"Submission blocked by QA gate: …"* listing exactly what is missing. Fix the docs / record the
QA sign-off, then advance again.

### 3.5 Record the government reference + mark govt-fee paid
On the order **Edit** screen, **Government submission** section:
- **Government reference** (`govt_ref`) — enter the embassy/authority reference. This is the gate that unlocks `awaiting_decision` and `delivered`.
- **Government fee paid** (`govt_fee_paid`) toggle — turn on once you've paid the authority; the **Government fee paid at** date field appears when it's on.

Remember to tell the customer: the government fee is **separate** from our service fee.

### 3.6 Deliver
When the outcome is in, advance `awaiting_decision → delivered` (govt-ref required), then
`delivered → won` to close as a win. Reaching **delivered/won** automatically fires the
**Delivered** email and the **Review request** email, and stamps `closed_at`.

### 3.7 Standard vs manual-review — how they differ
The lane (`EligibilityService`) is computed at intake:
- **Standard** *only when all are true*: UK nationality **and** UK residence **and** residency status = citizen **and** trip purpose = tourist **and** no prior refusal **and** not a minor. Standard orders are **priced from the destination's tiers** (service + govt fee) and flow straight through the pipeline.
- **Manual review** = everything else. **No fixed charge is taken** — fees stay blank. The order rests at `paid` and **cannot advance** until an agent clears it. You then issue a **bespoke quote** (3.10).

`visa_entries` and `dual_nationality` are captured but are **not** part of the routing decision.

### 3.8 Clearing a manual-review order (clear / refer)
On a manual-review order, use the **Clear / Refer** action (shield icon, only visible while the
lane is `manual_review`):
- **Clear (allow into pipeline)** → lane becomes `cleared`; the order can now advance past `paid`.
- **Refer (block)** → lane becomes `referred`; the order stays blocked at `paid`.
A **note is required** either way (it is stored on `eligibility_note`). An agent decision is sticky —
re-running intake never overwrites a cleared/referred lane.

### 3.9 Issue a refund
Use the **Refund** action (banknotes icon) on the order. It is hidden once an order is already
won/rejected/refunded. Enter the **amount** (defaults to the service fee) and an optional
**reason**, then confirm. `OrderService::refund()` records `refund_amount` / `refund_reason` /
`refunded_at`, transitions the order to **refunded**, logs the event, and fires the **Refunded**
email. (Honesty: the government fee, if already paid to the authority, may be non-recoverable — set
the refund amount accordingly and explain it.)

### 3.10 Issue a bespoke quote (manual-review pricing)
For a manual-review order, create the price in **Commerce → Quotes** (`QuoteResource`): link the
**Order**, set the **Amount (£)**, **Status**, **Sent at**, and the **Payment link** (the Stripe
Payment Link sent to the customer). A sent quote moves to `sent`; once the customer pays it becomes
`paid`.

---

## M4 — Handle problems (task #107)

### 4.1 The Barrier register
**Operations → Barriers** (`BarrierResource`). A barrier is anything blocking progress. Fields:
- **Order** — required for **case-scoped** barriers; leave blank for destination/all scope.
- **Title**, **Nature** (temporary/…), **Scope** (case/destination/all), **Status** (open/resolved), **Detected by** (agent/auto), **Rule key** (idempotency key for auto-detected barriers), **Guidance** (what to do).
Status badges: **open** = amber, **resolved** = green. Filter by status or nature. Work the open
list down; resolve a barrier once cleared.

### 4.2 Passport-validity & QA-gate blocks — how to resolve
- **QA / doc blocks** surface when you try to advance to `doc_review` (need ≥1 doc) or `submitted` (need full docs **and** QA sign-off). The red notification names the exact gap — upload the missing documents, record the QA sign-off, retry.
- **Passport validity** lives on the destination (`passport_validity_months`, default 6) and on the order (`passport_expiry`). If a passport is short-dated, raise a **case-scoped barrier** ("Passport expiring soon"), proactively update the client (4.3), and do not submit until resolved.

### 4.3 Proactive client updates
**Operations → Client updates** (`ClientUpdateResource`). Each update **must** link an **Order**
and a **Barrier**. Pick the **Channel** (default Email), write a **Subject** and **Message body**,
and set **Sent at** (leave blank if it's a draft). Use these to keep the customer informed while a
barrier is open — proactive contact is the standard, not the exception.

### 4.4 Rejection capture
When the government refuses, record it in **Operations → Rejections** (`RejectionResource`): link
the **Order**, choose the **Reason**, set **Recorded at** (defaults to now), and add **Detail**.
Then move the order to **rejected** via Advance stage. Keep the language honest — a refusal is the
government's decision, not ours.

---

## M5 — Comms (task #108)

### 5.1 Calls are the primary channel
Phone is the first line. The customer's number is on the order (**Phone** column, searchable; also
on the Edit screen). Log the gist of every call as a client update or note so the timeline stays
complete.

### 5.2 WhatsApp
WhatsApp is a supported contact channel on **Client updates** (channel select). (Automated
WhatsApp sending is a Phase-2 item — see M8.)

### 5.3 Lifecycle emails fire automatically
On every **real, allowed stage change**, `EmailService::onStageChange()` queues the matching
customer email — you do **not** send these by hand:

| Transition | Email |
|---|---|
| `→ submitted` | Order submitted |
| `→ awaiting_decision` | Decision update |
| `→ awaiting_docs` | Documents needed |
| `→ delivered` / `→ won` | Delivered **+** Review request |
| `→ refunded` | Refunded |

`paid`, `doc_review` and `rejected` send **no** customer email on transition. The **order_paid**
confirmation is owned by the **Stripe webhook** (genuine payment), not the pipeline. Every send is
**idempotent** — one email per (order, event) — and is written into the event log as
"Email sent: <event>".

### 5.4 Quick notes / CRM (HubSpot)
Orders sync to **HubSpot** automatically — on creation and on every status change
(`SyncOrderToHubSpot`, dispatched after commit). The HubSpot deal id is shown on the order. Keep
notes/updates in the system so CRM and the journey log agree.

### 5.5 What NOT to send
- **Never put passport numbers, document scans/images, or customer PII into AI tools.** The built-in AI assist (`AiService`) is **advisory only** and leak-gated: it sends only non-PII operational state (destination, tier, status, counts, redacted barrier titles). It never changes status, never approves/rejects, never emails anyone.
- Never imply we are the government, never promise a decision date, never guarantee approval.

---

## M6 — Daily rhythm + management (task #109)

### 6.1 The Production Line (kanban)
**Operations → Production Line** (`ProductionBoard`). One column per pipeline stage, left → right,
each capped at 25 cards (heading shows the true total, e.g. "Doc Review (42)"), ordered by
**next due** then most recently touched. Each card has an **Advance** button that moves the order
**one linear stage** through the same gated `transition()` — a blocked move shows a red notification
explaining why. (The board never refunds/rejects — use the Orders actions for those.)

### 6.2 Reports + CSV
**Operations → Reports** (`Reports`). Filter by **Created from / Created to** (defaults to this
month) and optional **Status**; a live count shows matches. **Export CSV** streams the columns
`ref, name, email, destination, tier, status, total, created`. Read-only — nothing is mutated.

### 6.3 The owner daily digest
A scheduled command (`ukv:owner-digest`, **08:00 daily**) emails the owner three actionable buckets
over **open** orders: **Manual review awaiting clearance**, **SLA-breached**, and **Awaiting
documents** (status `awaiting_docs` or a `docs_missing` blocker). It sends nothing on a quiet day.
Recipient comes from `UKV_OWNER_EMAIL`.

### 6.4 SLA & priority
SLA windows by tier: **premium 12h, express 24h, standard 72h** (default 72h). The Dashboard's
**Overdue (SLA breached)** stat and the owner digest both flag open orders past their window. The
**Priority** field (`OrderPriority`: normal/high/urgent) sorts urgent/high to the top — set it on
the order and watch the digest.

### 6.5 Each morning, check:
1. **Dashboard KPIs** — Open orders (with stage breakdown), Overdue/SLA, Revenue this month, **Manual review** awaiting clearance.
2. **Owner digest** in your inbox — work its three buckets.
3. **Production Line** — clear left-to-right, starting with overdue/urgent cards.
4. **Orders → Flagged for review** filter — review and clear any risk-flagged orders (advisory only; never auto-blocks a customer).
5. **Barriers** — work the open list; send client updates where a barrier is open.

---

## M7 — Setup & launch sequence (task #110)

The full runbook is **`DEPLOY.md`** at the app root — work it top to bottom; each step is a real
launch blocker. Highlights operators must know:

### 7.1 Keys & environment (`DEPLOY.md` §1–3)
Production `.env`: fresh `APP_KEY` (`php artisan key:generate`), `APP_URL`/`UKV_BASE_URL`, real
`MAIL_*`, `UKV_OWNER_EMAIL`. **Stripe** live keys + a webhook to `/stripe/webhook` for
`checkout.session.completed` (rehearse with `sk_test_…` first). **HubSpot** token (rotate the old
one). `ANTHROPIC_API_KEY` is optional — the AI layer is a safe no-op without it.

### 7.2 Queue worker & cron (`DEPLOY.md` §5)
Emails and HubSpot sync are **queued**; the digest/purge/reconcile are **scheduled**. You need a
**queue worker** (`php artisan queue:work`) and a **cron** running `schedule:run` every minute. On a
shared host without a worker, set `QUEUE_CONNECTION=sync`. Scheduled jobs:
`ukv:purge-documents` (daily), `ukv:reconcile-stripe` (06:00), `ukv:owner-digest` (08:00).

### 7.3 2FA via My Profile (`DEPLOY.md` §6)
Each operator enables **two-factor authentication** under **My Profile** (Filament Breezy,
per-user TOTP). Do this before handling real PII.

### 7.4 Verify fees — commercial/legal blocker (`DEPLOY.md` §7)
Seeded fees, government fees, processing times, passport-validity and required-docs are
**placeholders**. Verify **every destination** against gov.uk and the issuing authority and update
via **Catalogue → Destinations** *before* taking real payments. Each destination holds the govt fee,
the three tier prices, max stay, passport-validity months, IDP convention, and required docs.

### 7.5 Create ops users + roles
Create the first admin via tinker (see `DEPLOY.md` §4 — don't reuse demo creds). Assign roles
deliberately: **Admin** (full + setup), **Agent** (full day-to-day write), **Viewer** (read-only —
good for stakeholders). Viewers are blocked from create/edit/delete on every resource.

### 7.6 Test-mode smoke run (`DEPLOY.md` §8)
Before go-live: home → checker → `/apply` → **standard lane** → Stripe **test card
`4242 4242 4242 4242`** → webhook fires → order shows `paid` + confirmation page → `/track` shows
the stage → order_paid email in the log → order visible in `/admin`. Then rehearse the
**manual-review lane** (callback → clear → bespoke quote). Then switch to live keys and retire
WordPress.

---

## M8 — Growth (task #111)

### 8.1 SEO surface
The public site is the content silo (`routes/web.php`): the **destination money pages**
(`/visa/{slug}`, DB-driven from Catalogue → Destinations), the **guides** (`/guides`,
`/guides/{slug}`), the comparison and tools pages, an XML **sitemap** (`/sitemap.xml`,
`SitemapController`), and **schema/JSON-LD** emitted on sitemap/review surfaces. Keep destination
data accurate and guides fresh — accuracy is both SEO and compliance.

### 8.2 Reviews & feedback
**Operations → Feedback** (`FeedbackResource`) captures ratings (1–5), comments, source, and a
**story consent** flag. Public reviews render at `/reviews`. To encourage reviews, use the
**Issue review-incentive code** action on a delivered order (gift icon) — it mints a next-order
discount tied to the customer's email, sent with the review-request email. Codes live in
**Commerce → Discounts**.

### 8.3 Conversion levers
- **Returning-customer loyalty discount** — applied automatically on the standard lane for repeat customers (a `loyal` discount row + audit note).
- **Review-incentive codes** — next-order discounts (above), tracked in Discounts (single-use, `used` flag).
- **Bespoke quotes with Stripe Payment Links** — fast close for manual-review cases.
- **Priority/express tiers** — sell faster *handling* (never a faster decision — stay honest).

### 8.4 Phase-2 backlog
- **WhatsApp** automated sending (channel already modelled on Client updates).
- **Insurance introducer** (`insurance_required` flag is already captured at intake).
- **Fraud flags** — the advisory risk guard already scores orders (`risk_flag` / `risk_score` / `risk_reason`); expand the rules and the morning review.
- **AI vision** document review (`AiService::reviewDocumentImage`) — the one leak-gated path that sends a single image for **quality** signals only; still advisory, still no PII extraction.

---

### Compliance footer (say it on every case)
Independent service, not the government • service fee and government fee are separate •
express speeds our handling, not the government's decision • IDP is guided self-service (PayPoint,
in person) • we never guarantee approval • never send passport numbers or scans to AI.
