# Staff training manual — running the UKV production line

How the team operates the system day-to-day. Pairs with the runbook (process), recipes (9-lens per step), and
the flags reference (what alerts mean). Compliance always: independent service, not a government website; service
fee separate; express speeds our handling not the decision; no approval guarantee; IDP = guided self-service.

## 1. Where you work (wp-admin)
- **Production Line** (top menu) — the live board: every open order in a column per stage. Your daily home screen.
- **Orders** — the full list; open any order for its detail screen.
- **Cockpit** — KPIs: open orders, revenue this month, SLA breaches, high-risk count.
- **Reports** — monthly summary + CSV export.
- **Dashboard widgets** — My digest (your pending actions), Success intelligence, Rejection causes, Open barriers, Improvement suggestions.
- **Runbook / SOP** (under Production Line) — the full process reference.
- **Settings** — UKV Contact (numbers), UKV Zapier, UKV AI Assist (key); **Tools → Pre-launch** (readiness), Doc retention, Stripe reconcile, Supply chain.

## 2. A day in the life
1. Open the **Production Line** board. Work left-to-right; SLA-breach + high-risk cards sort to the top.
2. Check **My digest** widget: orders due today, breaches, high-risk.
3. For each order you own, open it and read the **Stage SOP & troubleshooting** box — it tells you exactly what to do at this stage (and the 9-lens recipe for the full picture).
4. Do the action; log a **journey note** (or use Quick note from the list). Update the **status** to advance the order — the **gates** will stop you if it's not ready (e.g. docs incomplete, no sign-off).
5. Use **Call / WhatsApp** as primary; email is secondary.

## 3. The order screen — what each box does
- **Lead Journey** — the story: critical header (stage, blocker, next action, due, priority, travel date, risk, value) + the dated note timeline. Add a note after every contact.
- **Barriers (live)** — any blocker affecting this order; log new ones; destination-wide barriers appear automatically.
- **Stage SOP & troubleshooting** — your do/watch/next + the 9-lens recipe + solutions for this order's blockers.
- **Government submission** — record the govt reference + fee-paid + appointment ref/date.
- **Appointment** — book the centre/slot, set status, generate the customer pack.
- **QA sign-off** — tick only when docs are complete + checked; required before you can submit.
- **Owner** — who's responsible. **Trip group** — link travellers on the same trip. **Story consent** — if the customer agreed to a testimonial.
- **AI doc review** — run the advisory check; it never auto-rejects, you decide.
- **Premium slot / Passport return / Refund** — add-ons + logistics + the refund action.

## 4. Moving an order forward (the gates)
You change `status` to advance. The system blocks you if criteria aren't met:
- → **Doc review** needs at least one document.
- → **Submitted** needs 100% required docs **+ your QA sign-off** (the QA gate).
- → **Awaiting decision / Delivered** needs the government reference recorded.
If blocked, an on-screen notice tells you the missing item; the status reverts. Fix it, then advance.

## 5. Handling problems
The SOP box surfaces the right solution for the order's blocker. Common ones: short passport validity → pause +
advise renewal; no appointment slots → keep checking + offer premium; portal outage → log a destination barrier
(it fans an update to all affected); near travel, no decision → escalate + honest contingency.

## 6. Comms rules
- Call within ~1 working hour of a new order. Retry 3×/24h + WhatsApp/email, persist near-travel, then pause+notify.
- Update customers only on real news. Every stage has an automatic email (confirmation, docs-needed, submitted, decision, delivered, review).
- Never promise a government decision or timeframe; never imply we are the government.

## 7. Closing + aftercare
Deliver → confirm receipt → review request (with a next-order discount) → the system purges stored scans at 90
days. Rejections: capture the structured reason (it feeds the success dashboard + improvement suggestions);
refund our service fee (the government fee is non-refundable).
