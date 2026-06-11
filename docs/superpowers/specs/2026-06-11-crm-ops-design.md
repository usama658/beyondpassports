# CRM + Ops — Sub-project Design Spec

**Date:** 2026-06-11 · **Parent:** `2026-06-11-uk-outbound-evisa-site-design.md` (subsystem #5 of 6)
**Depends on:** /apply funnel (#4) — receives its order payload. **Pairs with:** visa JSON (#2) for SLA/processing data.

## Goal
Turn a paid order into a tracked, fulfilled visa/eVisa/ETA delivery. A Pipedrive deal = one order, advanced through a fulfilment state machine, with Zapier automating customer comms and the back-office handling manual government-portal filing. Grounded in 2025–26 facilitation-industry research (see memory `express-not-faster-govt`).

## Scope correction (IDP)
IDP is **guided self-service** (subsystem #6), not done-for-you. **No IDP fulfilment deals enter this CRM in v1.** The pipeline handles **visa/eVisa/ETA fulfilment only**. (IDP self-service may emit marketing leads later; concierge tier deferred.)

## Decisions (locked)
| Item | Choice |
|---|---|
| CRM | **Pipedrive** — one deal per order. |
| Pipeline | Paid → Awaiting docs → Doc review (verify gate) → Submitted to authority → Awaiting decision → Approved/Delivered → Won. Branches: Rejected, Refunded. |
| Express | Priority **flag** on the deal (faster our handling/queue), **not** a separate stage and **not** a faster government decision. |
| Integration | Stripe → (webhook/Zapier) → Pipedrive + transactional email (Postmark/SendGrid). |
| Fulfilment key | **Passport number** stored on the deal (ETA issues no document). |
| Docs | Links to encrypted private bucket (expiring signed URLs); auto-delete N days post-delivery (GDPR). |

## Pipeline stages (state machine)
| Stage | Enters when | Back-office action |
|---|---|---|
| **Paid / New** | Stripe success → deal created (payload from #4) | auto; ops notified |
| **Awaiting docs** | required doc missing/illegible | chase customer |
| **Doc review** (gate) | all docs present | human verify vs destination requirements (`visa.requirements`); **cannot advance until verified** — the core value-add |
| **Submitted to authority** | ops files with the destination eVisa/ETA portal | record government reference no. |
| **Awaiting decision** | filed | monitor portal |
| **Approved / Delivered** | result received | eVisa → email PDF; ETA → email approval (linked to passport) |
| **Won (closed)** | delivery confirmed | — |
| **Rejected** (branch) | authority refuses | notify + service-fee refund per sliding scale |
| **Refunded** (branch) | cancel/refund | Stripe refund + reason logged |

## Zapier automations (stage → action)
- Stripe success → create deal + **order-confirmation email** (includes compliance disclaimer: not government-affiliated, service fee excludes government fee).
- → Awaiting docs → "we need X" email + ops task.
- → Submitted → "your application is being processed" email.
- → Approved/Delivered → **eVisa**: email PDF · **ETA**: email approval + "no document is issued — it is linked to your passport; travel on the same passport"; + review request.
- → Rejected → explanation + service-fee refund per policy.
- → Refunded → Stripe-refund confirmation email.

Customer email = transactional service (Postmark/SendGrid) with branded templates + Stripe receipts.

## Refund logic (from research)
- **Government fee: non-refundable** (paid to authority, irreversible).
- **Service fee sliding scale by stage:** ~100% before Submitted → ~75% during Doc review → **0% once Submitted to authority**.
- **24-hour cancellation window** (government fee refundable only within it / before filing).
- Encoded as a deal-stage-driven rule the back-office follows; Stripe refund executed manually.

## Document handling (security)
- Deal stores **links**, never the files. Ops opens via expiring signed URL.
- Bucket encrypted at rest; access logged.
- Auto-delete documents N days after delivery (GDPR retention, parent §9). Passport number retained as fulfilment record per retention policy.

## Back-office / ops
- Pipedrive board is the ops console; each deal carries a per-stage checklist.
- Manual government-portal filing per destination; ops records reference numbers.
- **SLA timers per tier** from JSON `visa.processing` (standard_days / express_hours) — flag deals breaching target.
- Express deals surfaced first via the priority flag.

## Out of scope
Funnel/payment capture (#4 — produces the payload) · IDP (#6, self-service, no deals) · analytics dashboards (Foundation #1 owns GA4/Looker; this spec only fires the `purchase` event upstream in #4).

## Acceptance criteria
- A Stripe test-mode payment creates a Pipedrive deal with the full #4 payload (order ref, product, dest, tier, amount, line items, applicant, document links, passport number).
- Deal cannot advance past Doc review without a "verified" check.
- Each stage transition fires the correct customer email; ETA vs eVisa delivery emails differ correctly.
- Refund at each stage applies the correct sliding-scale amount; government fee never refunded.
- Document links expire and auto-delete per retention rule; no raw files stored in Pipedrive.
- Express deals carry the priority flag and surface ahead of standard in the ops view.

## Open items
- Final transactional email provider (Postmark vs SendGrid).
- Webhook host for Stripe→Pipedrive (small serverless function vs Zapier-only) — coordinate with #4.
- Encrypted bucket choice + retention window N (align with privacy policy).
- Exact service-fee refund percentages per stage (policy sign-off).
