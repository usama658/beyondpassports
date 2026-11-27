# Appointment board — dynamic slots (LOCKED spec v1.2)

Status: **LOCKED, implemented and live.** Owner signed off 2026-09-23; reset cadence changed
from weekly to every 3 days on 2026-11-28 (owner request).
Build target: staging first, then live after review — already done.

This is the single source of truth for the dynamic appointment-availability board.
Do not change these rules without owner sign-off; update the version + date here when you do.

---

## 1. Pool & seeding
- **70 total slots per 3-day period**, split across all Schengen countries shown on the board.
- **Randomised each period**: a period-seeded generator produces a **different split every period**, so almost every country's number changes.
- **Weighted toward popular** countries (France, Spain, Italy, Germany, Greece, Netherlands, Portugal get proportionally more) and **floor ≥ 1** for every country — random, but never zero and never a dead-looking board at seed.
- Split is **fixed within the 3-day period**, derived deterministically from the period's seed key → varies every reset, needs no cron.

## 2. Decrement trigger
- **Per inquiry** (a lead event — WhatsApp CTA / form / `bpLead`), **not** per pageview.
- Country taken from the **lead's destination** (country page they came from, or country picked in the flow — already captured in lead data). Not geo-IP.
- Each qualifying inquiry: **that country −1 and the total −1**.
- **Dedup**: one decrement per unique visitor per country per period (a person clicking twice ≠ 2 slots gone).

## 3. Shared, site-wide, single source
- Counts are **global shared state** — the **same numbers on every board across the whole site** (home, /schengen-visa, all LPs). One data source, always consistent.

## 4. Floor & zero state
- Never below 0. When a country reaches 0 → **display exactly as today's board shows a no-availability country**. No design or copy change.

## 5. Reset
- **Every 3 days**: fresh 70, fresh random split, all decrements cleared — automatic via the period key, counted from a fixed epoch (`App\Support\SlotBoard::EPOCH`), not calendar weeks. **No cron dependency** (must not silently break on the shared host).

## 6. Storage & performance
- Counts in **cache (fast) backed by DB (durable)** — survives restarts, consistent across all devices/visitors, no crash risk.
- **Server-rendered into the existing board** — **no new JS, no extra assets, no page-size or page-load increase.**

## 7. Rollout
- **Staging first**, then live after owner review. (Matches the standing "new board design → staging first" rule.)

---

## Compliance note (on record)
This is a **simulated countdown** (invented numbers that fall to create urgency). Raised as a
risk under the **UK DMCCA 2024 / CAP Code** (fake scarcity / false urgency is a banned
practice; CMA-enforceable, fines up to 10% global turnover). **Owner chose to proceed** with
the simulated mechanic despite the flag (2026-09-23). Honest alternatives that were offered and
declined for now: drive the board from real `/admin/update-availability` data; frame as
"typical weekly capacity"; or label as a general demand indicator.

## Open build details (to decide at implementation)
- Exact popular-country weights + the weighted-random allocation algorithm (sum=70, floor≥2).
- Which country set the board renders (featured-8 + full A–Z grid) and how each tile reads the count.
- Cache store + key shape (e.g. `slots:{isoWeek}:{country}`), DB fallback table, dedup key
  (`slotdec:{isoWeek}:{country}:{visitorHash}`).
- Where the decrement hooks into the existing lead path (`bpLead` / form capture / wa-click),
  keyed to lead destination.
