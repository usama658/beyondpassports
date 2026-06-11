# Smart Stories — Barriers, Client Updates & Helpful Content — Design Spec

**Date:** 2026-06-12 · **Parent:** Smart Orders & Ops Hub (`2026-06-12-smart-orders-hub-design.md`) · **Depends on:** Order CPT (`ukv_order`), Journey log (P7, built), Pods destinations, HubSpot CRM, glue (`ukv-forminator-glue.php`).

## Goal
Turn real case data into two outputs: **(a) private, proactive client updates** when a barrier threatens an application, and **(b) public anonymised helpful content** that lifts SEO/trust without leaking client PII or business secrets. A shared **barrier register** is the spine. Everything is **draft → human approve → send/publish; never auto**.

## Architecture overview
- **`ukv_barrier` CPT** = single source of truth for every barrier (case-level or destination-wide). The "dynamic" model: barriers are stored **once** and **surfaced live by query** — on an order, on a destination, in the dashboard — never copied. Update or close one barrier → every affected case reflects it instantly.
- **Journey log (P7, built):** per-order note timeline + critical header. Barriers reference it; updates append to it.
- **Update drafter:** given an open barrier, drafts a per-client message (email / WhatsApp link / call task) for each affected open order. Human approves → send.
- **Content engine:** given a resolved barrier or a case, generates an anonymised WP **draft** post through two redaction layers. Human approves → publish.
- **AI polish (optional, key-gated):** Claude rewrites guidance + drafts into plain, on-brand prose. Rules-based templates work without the key.

## 1. Barrier register (`ukv_barrier` CPT)
Admin-only CPT. Fields (post meta):
- `nature` — `temporary` (portal outage, backlog, seasonal surge, doc re-check) | `permanent` (policy change, new requirement, route closed).
- `scope` — `case` | `destination` | `all`.
- `destination` — slug (for `destination`/`all` it drives fan-out; for `case` it is informational).
- `order_ref` — set when `scope=case` (links to one `ukv_order`).
- `guidance` — plain-English next step for the client.
- `status` — `open` | `resolved`.
- `detected_by` — `agent` | `auto` | `destination`.

**Sources (all of the above):**
1. **Agent logs** — a "Log barrier" control on the order's Lead Journey meta box (and a standalone New Barrier screen for destination-wide).
2. **Auto-detect (wp-cron daily, reuse SLA cron):** rules create `open` barriers with `detected_by=auto`:
   - SLA breached → temporary, case.
   - Passport validity short of destination requirement (from Pods) → permanent-for-this-trip, case.
   - High-rejection destination + open blocker near travel date → temporary, case.
   Auto-detect is **idempotent** — re-running the cron does not duplicate an existing open barrier for the same (order, rule).
3. **Destination-wide** — `scope=destination` barrier stands alone; fan-out is a **live query** of open `ukv_order` rows for that destination at display/send time (never copied onto them).

**Dynamic surfacing (no duplication):**
- On an **order**: show case barriers (`order_ref` match) + destination barriers (`destination` match, `scope` in destination/all), queried live.
- On the **dashboard**: top open barriers by destination + count of affected open orders.

## 2. Proactive client updates (private)
- For an open barrier, the drafter builds one message per affected open order: *what changed · temporary or permanent · what you must do · our next step (`guidance`)*.
- Channels: **email** (wp_mail/SMTP at prod), **WhatsApp link** (pre-filled wa.me via existing number option), **call task** (a journey note + next-action so an agent rings).
- Destination-wide barrier → drafts for every affected open order at once.
- **Human approves before send.** Each send appends a journey note to that order (audit trail). No PII risk — goes to that client only.

## 3. Public helpful content (anonymised)
Anonymised case studies + problem→solution guides → WP **draft** posts (reuse the existing publish pattern + RankMath meta). Two redaction layers, **both enforced before publish**:
- **Privacy (protect client):** strip ALL PII — name, email, phone, passport number, exact dates, `order_ref` → generalise to "a UK traveller to {destination}".
- **Competitor-safe (protect business):** never expose internal margins, supplier/agent names, processing routes, volumes, or operational playbook. Publish reader value only — *what the problem was + how to handle it* — never *how we operate*.

**Privacy model = both tiers (combo):**
- **Anonymise-only:** any case/barrier → general guide, no consent needed (no PII present).
- **Consented testimonials:** a checkout/order opt-in field (`ukv_story_consent`) unlocks richer, still-anonymised testimonials, only for cases that ticked consent.

All public content: **DRAFT → human approve → publish. Never auto-publish.**

## 4. AI polish (optional, needs `UKV_ANTHROPIC_KEY`)
Claude rewrites barrier `guidance` and content drafts into plain, on-brand prose, and proposes a next-best-action per case from similar past cases. Falls back to rules-based templates without the key. Advisory only — human still approves.

## 5. Integration test (end-to-end)
A scripted pass (wp eval-file + assertions) that exercises the whole chain on seeded data:
1. Seed an order + a destination barrier (Egypt) → assert it surfaces on every open Egypt order via live query, zero duplication.
2. Run auto-detect cron twice → assert idempotent (no duplicate open barriers).
3. Draft client updates for the barrier → assert one per affected order, correct guidance, journey note appended on (simulated) send.
4. Generate public content from a resolved barrier → assert output contains **no** PII tokens (name/email/passport/ref/exact dates) and **no** competitor-confidential tokens (margin/supplier/route/volume), and lands as `draft` not `publish`.
5. Resolve/close the barrier once → assert it disappears from all affected orders' live surface.
Build/test sped up with **subagents** (parallel content drafting + parallel redaction-assertion checks).

## Build phases
11. **Barrier register** — `ukv_barrier` CPT + agent-log control + auto-detect cron (idempotent) + dynamic surfacing on order/destination/dashboard. Free.
12. **Proactive client updates** — drafter (email/WhatsApp/call-task) + destination fan-out + approve-to-send + journey-note audit. Free (+SMTP at prod).
13. **Public content engine** — anonymise-only generator with both redaction layers + draft→approve→publish + RankMath meta. Free.
14. **Consented testimonials** — `ukv_story_consent` checkout field + richer testimonial drafts from consented cases. Free.
15. **AI polish** — Claude rewrite + next-best-action; rules-based fallback. Needs Anthropic key.
16. **Integration test** — end-to-end scripted pass (the 5 checks above), subagent-parallelised. Free.

## Dependencies / open items
- `UKV_ANTHROPIC_KEY` (P15 only) — user provides; DB option, not git.
- SMTP at production for real email sends (local XAMPP won't deliver).
- New Pods field per destination: required passport validity (for auto-detect rule) — confirm it exists or add.
- WhatsApp number option (already set, sample).

## Acceptance criteria
- A barrier is stored once; updating/closing it reflects on **all** affected orders with **no** duplicated records.
- Auto-detect cron is idempotent (second run creates no duplicates).
- Destination-wide barrier drafts one client update per affected open order; each approved send leaves a journey-note audit trail.
- Public content drafts contain zero PII tokens and zero competitor-confidential tokens, and never auto-publish (always land as `draft`).
- Consented testimonials are produced only from cases with `ukv_story_consent` set.
- The end-to-end integration test passes all 5 checks.

## Out of scope
WhatsApp Business API automation (separate paid sub-project); building the customer funnel (done); HubSpot pipeline setup (done); paid email-marketing platform (SMTP/HubSpot workflows cover lifecycle).
