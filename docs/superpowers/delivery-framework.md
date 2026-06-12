# Delivery framework — the repeatable fulfilment flow (internal SOP + customer guides)

One backbone you run for every order, with per-product branch notes (eVisa · ETA · full visa · IDP). Each stage
maps to an `ukv_status`, an email event, and the journey log. Compliance threads throughout: independent service,
not a government website; our service fee is separate from the government fee; express speeds **our** handling,
not the government's decision; we never guarantee approval; **IDP = guided self-service** (we guide; the customer
collects it in person at PayPoint).

## The backbone (6 stages)

| # | Stage | `ukv_status` | Email event | SLA target |
|---|---|---|---|---|
| 1 | Intake | `paid` | order_paid | same day |
| 2 | Document collection | `awaiting_docs` | docs_needed | 24h chase |
| 3 | Review & prepare | `doc_review` | — (internal) | per tier |
| 4 | Submit | `submitted` | submitted | per tier |
| 5 | Awaiting decision | `awaiting_decision` | decision | govt-dependent |
| 6 | Deliver + aftercare | `delivered` / `won` | delivered + review_request | same day as grant |

(Exceptions: `rejected` → outcome guidance + options; `refunded` → per policy. Both are barriers + comms.)

---

### Stage 1 — Intake (`paid`)
- **Internal:** confirm product + destination + travel date; create the order record (auto on charge); set tier/SLA; open the journey log.
- **Required from customer:** payment (done); confirm trip details.
- **Customer guide/comm:** order confirmation email ("we've got it; here's what happens next; here's the documents we'll need"). Link to the **Track** page.
- **Barrier risks:** wrong destination/product selected → fix before proceeding.

### Stage 2 — Document collection (`awaiting_docs`)
- **Internal:** send the exact required-docs list (per destination); chase at 24h if missing (auto-chase).
- **Required from customer:** passport bio page scan; passport-style photo; travel dates; any destination extras (e.g. onward ticket, insurance for Zanzibar, accommodation).
- **Customer guide:** "How to send your documents" (clear photo/scan tips) + the per-destination requirements list.
- **Barrier risks:** missing/blurred docs; **passport validity short** of the destination requirement → flag now, advise renewal.

### Stage 3 — Review & prepare (`doc_review`)
- **Internal:** run the **AI document review** (expiry vs required validity, name match, photo spec, legibility) — advisory; a human confirms. Fix issues with the customer. Prepare the application on the official portal.
- **Required from customer:** corrections if flagged (e.g. new photo).
- **Customer guide:** only if action needed — "we spotted X, please send Y" (proactive update).
- **Barrier risks:** eligibility issue; photo spec fail; name mismatch.

### Stage 4 — Submit (`submitted`)
- **Internal:** submit to the official system; record reference; pay the government fee from the collected total.
- **Required from customer:** none (we handle it).
- **Customer guide:** "Your application has been submitted" email — what to expect + typical timeframe (cautious language, no guarantee).
- **Barrier risks:** portal outage/backlog (temporary, destination-wide barrier → fan-out update to all affected).

### Stage 5 — Awaiting decision (`awaiting_decision`)
- **Internal:** monitor; respond to any authority query; keep the customer posted.
- **Required from customer:** respond quickly if the authority asks for more.
- **Customer guide:** "Update on your application" only when there's real news.
- **Barrier risks:** authority request for info; near travel date + high-refusal destination → prioritise.

### Stage 6 — Deliver + aftercare (`delivered` / `won`)
- **Internal:** receive the grant; deliver to the customer; archive (Drive folder per order_ref via Zapier); mark delivered.
- **Required from customer:** confirm receipt.
- **Customer guide:** "Your visa/authorisation is ready" + how to use it on arrival + entry tips + the request-a-review email.
- **Barrier risks:** none — but capture a testimonial (consented) for content.

---

## Per-product variants (where the backbone bends)

- **eVisa** (Egypt, India, Turkey, Kenya, Tanzania, Sri Lanka, Vietnam, Cambodia, Saudi, Oman, etc.):
  fully online. Docs = passport scan + photo. Delivery = approved e-visa PDF by email to print. Standard backbone.

- **ETA / ESTA / eTA** (USA ESTA, Canada eTA, NZ NZeTA, ETIAS-future): a quick **authorisation linked to the
  passport — not a physical document/visa**. Often fast (hours–days). Stages 2–3 are light (passport details +
  a few questions; usually no photo). Delivery = confirmation + "it's tied to your passport; carry the printout."

- **Full / sticker visa** (where applicable): may need an **appointment, biometrics, or embassy submission**.
  Insert a sub-stage between 3 and 4: "Book appointment / attend biometrics." Longer SLA. Customer guide covers
  what to bring to the appointment.

- **IDP (International Driving Permit)** — **guided self-service, in person at PayPoint** (photocard licence
  holders are exempt for some countries; check). We do NOT obtain it for the customer. Flow: intake → we send a
  step-by-step guide (which IDP type — 1926/1949/1968 — for the destination, what to bring: licence + photo +
  fee) → customer visits PayPoint → done. Our value = the right-permit guidance + checklist, not fulfilment.

---

## How it plugs into what's built
- **Statuses** drive the stage; moving an order's `ukv_status` fires the matching **email event** automatically.
- The **journey log** records each touch; **barriers** carry the per-stage risks + client guidance.
- **AI doc review** is Stage 3; **proactive client updates** cover Stages 2/4/5; **review request** is Stage 6.
- **Drive/Sheet (Zapier)** archives at Stage 6.

## Customer-facing "What happens next" (the short version to publish)
1. You apply & pay → 2. We confirm what's needed → 3. You send documents → 4. We check & submit →
5. We track the decision → 6. We deliver your visa + how to use it. *Independent service — not a government
website. We never guarantee a government decision; we make sure your application is right.*
