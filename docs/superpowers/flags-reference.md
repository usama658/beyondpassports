# Flags reference — every badge / alert → meaning → action

What each signal in the system means and what to do about it. For staff working the Production Line + order
screens + dashboards.

## On the order / board
| Flag | Where | Means | Action |
|---|---|---|---|
| **SLA pill — red** | board card, cockpit | Past the tier's processing target (24h express / 12h premium / 72h default) | Prioritise; it auto-escalates to the owner. Move it forward or log a barrier explaining the delay. |
| **SLA pill — amber** | board/order | Approaching the SLA due time | Action it before it breaches. |
| **Risk flag** | order header, board | Auto-set: high-rejection destination + open blocker (near travel) | Treat as priority; resolve the blocker before submission. |
| **Blocker (header dropdown)** | order | A named hold: docs missing / payment pending / eligibility / customer deciding | The SOP box shows the fix; clear it to unblock. |
| **AI Docs badge — green "Docs OK"** | board/order | AI advisory check found no issues | Still confirm yourself; AI is advisory, never auto-rejects. |
| **AI Docs badge — amber "Review: N"** | board/order | AI flagged N issues (expiry/name/photo/legibility) | Open the verdict, fix with the customer, re-check. |
| **AI Docs badge — grey "Not reviewed"** | board/order | Review not run yet | Run "AI doc review" once docs are in. |
| **Barrier (live) — Temporary** | order | A passing issue (portal down, backlog, doc re-check) | Follow the guidance; update the customer; it clears when resolved. |
| **Barrier (live) — Permanent** | order | A standing change (policy/requirement/short validity) | Customer must act; advise clearly. |
| **QA gate blocked notice** | order (on save) | Tried to submit without 100% docs + sign-off | Add the missing docs, tick QA sign-off, then submit. |
| **Stage gate blocked notice** | order (on save) | Tried to advance without the stage's entry criteria | Read the notice (e.g. "needs a document" / "needs govt reference"); meet it, then advance. |

## On dashboards / tools
| Flag | Where | Means | Action |
|---|---|---|---|
| **High-risk open count** | cockpit, success widget | Open orders matching a high-rejection pattern | Work these first; they're most likely to fail. |
| **Top rejection causes** | Rejection-causes widget | The reasons applications are being refused, by destination | Feed the fix (Improvement suggestions); tighten that destination's checklist. |
| **Improvement suggestion** | widget | A recurring rejection reason for a destination | Act on it (e.g. add an eligibility pre-screen, clearer photo example). |
| **Open barriers by destination** | Barriers widget | Where the most blockers are right now | Investigate clusters (often a portal/centre issue). |
| **Reconcile: unmatched charge** | Tools → Stripe reconcile | A successful Stripe charge with NO matching order (missed webhook) | Create/repair the order manually — this is revenue at risk. |
| **Pre-launch: red item** | Tools → Pre-launch | A go-live blocker (exposed HubSpot token, sample numbers, no SMTP, file-edit allowed) | Resolve before launch — esp. **rotate the HubSpot token**. |
| **Documents pending purge** | Tools → Doc retention | Closed orders whose scans are past the 90-day window | The cron purges automatically; "Run purge now" if needed. |
| **Owner digest counts** | My digest widget | Your open / due-today / SLA-breach / high-risk orders | Your worklist for the day. |

## Compliance flags (always watch)
- Never imply we are the government or guarantee a decision/approval.
- Express speeds **our** handling, not the government's decision — say it that way.
- The government fee is separate from our service fee and is **non-refundable** once paid.
- IDP is guided self-service collected in person at PayPoint — we advise, we don't obtain it.
- Public/anonymised content is **draft-only** and auto-blocked if it contains PII or competitor detail.
