# Appointment Availability Playbook — Schengen slot checking (Beyond Passports)

How to check Schengen appointment availability, log it, and surface it on the site — the
compliant, rate-limit-safe way. Learned the hard way on 2026-07-29 (5× rate-limit + 1 account
restriction in one session). **Read the "One rule" section before touching any portal.**

Tracking lives in the appointments Google Sheet, 3 tabs:
1. **Schengen Availability** — one rolled-up soonest slot per country (feeds the website)
2. **Slot Detail** — long-format: one row per country × centre × sub-category check
3. **Portal Accounts** — logins + gate status + phone per operator

---

## 0. The One Rule (non-negotiable)

**ONE category check per country, then STOP and move to the next country.**

Toggling sub-categories or centres inside one country is what trips the rate-limit **every single
time**. On 2026-07-29, ~6–8 toggles per country tripped it 5×; the repeats escalated it to a harder
**account-level** restriction. First check shows a date → log it. First check shows "no slots" →
log empty and move on. **Never open a second category.**

Error codes:
- **429201** = temporary rate-limit. ~2-hour cooldown, auto-resets. Per-account (a fresh country
  account still works while another is cooling down).
- **429001** = account-level "unusual activity" restriction on the user ID. Harder, no published
  reset (~24–48h+), and it *re-extends every time you poke it early*. Escalated to from repeated
  429201s. If you hit this: **stop for 48h**, then ONE careful test; if still blocked, use the
  operator's Contact Us with the user ID.

Realistic throughput: **3–4 countries per session, one check each.** A full 20-country sweep is a
multi-day drip, not a one-sitting job — and honestly isn't worth it (see §6).

---

## 1. Which operators are even checkable ("Sweepable" column)

| Operator | Sweepable? | Why |
|----------|-----------|-----|
| **VFS Global** (most countries) | ✅ Yes | Login → book-appointment → calendar shows earliest slot. No application needed. |
| **BLS International** (Spain) | ✅ Yes | Separate portal, own calendar. Not affected by a VFS rate-limit. |
| **TLScontact** (France, Germany, Belgium, Switzerland) | ❌ Gated | Calendar is locked behind a completed application / reference (France needs a **France-Visas** `FRA1ED…` ref from france-visas.gouv.fr first). No genuine applicant = no calendar. |
| **GVCW** (Greece) | ❌ Gated | ToS: **individual applicants only, no professional/bulk use** — excess use cancels reserved slots. Booking needs the applicant's real passport. |
| **Embassy direct** (Luxembourg, Poland, Slovakia, Romania) | ❌ Gated | Book by email / e-application first; no open calendar. |

**Gated operators are never swept.** Their site availability stays **"enquire"**; you only book
them **per real client** (their data, their consent, one genuine booking = individual use, allowed).

---

## 2. Account setup (one-time, per operator)

- **VFS = one account per country** (per-country web logins, even though the app is one). Register
  each at its country URL.
- **Shared login email:** `appointments@beyondpassports.co.uk` works for every VFS/BLS/GVCW account.
  OTP/verification lands there → forwards to the `hello@` master inbox.
- **Shared password scheme:** stored per-row in Portal Accounts (e.g. `Vfs4@Nld!91`). Low-value
  ops logins; fine in the sheet, don't reuse elsewhere.
- **Contact name:** Chloe Adams (Appointments & Client Coordinator) — keep it consistent.
- **Phone:** the official number `+44 7882 747584`.
- On signup, record in Portal Accounts: Created date, Username (GVCW logs in by username not email),
  Status, Phone.

---

## 3. Doing a check (per country)

1. Confirm the country is **Sweepable = VFS/BLS** in the sheet. If gated → skip.
2. Go to the country's portal URL (Portal Accounts). Log in with `appointments@` + its password.
3. Book Appointment → pick **one** centre + **one** sub-category (Tourism/short-stay is most
   representative) → read the **earliest available date** (or "no slots").
4. **STOP.** Do not change centre or category. Move to the next country.
5. Paste the result back to be logged:
   ```
   Netherlands: 2026-08-17 (Edinburgh, Others)
   ```

---

## 4. Logging (what goes where)

- **Slot Detail** (append one row): Country · slug · Centre · Sub-category · Soonest (YYYY-MM-DD or
  NONE) · Checked · Notes. This is the granular record — one row per data point.
- **Schengen Availability** (update the country's row): `Next date` = soonest across all its checks;
  `Band` = good (plenty) / limited (few) / ask (unknown or empty); `Checked` = date; keep the
  Sweepable / Centres / Processing columns.
- **Portal Accounts**: update Status ("Registered + checked", "429001-restricted", etc.).

Bands: **good** = slots available near-term, **limited** = scarce/one, **ask** = unknown or none →
site shows "enquire".

---

## 5. Surfacing on the website (the honest pipeline)

```
Slot Detail / Schengen Availability sheet
   → paste block into Admin ▸ Update Availability
   → AvailabilityService::parseBulk() → setSnapshot()
   → CentreAvailability (DB)  →  AvailabilityService::byDestination()
   → public /schengen-visa board
```

Paste-block line format (one per country):
```
<slug>: <YYYY-MM-DD> <good|limited>     e.g.  italy: 2026-08-06 good
<slug>: ask                            (resets to "enquire")
```

**Self-correcting by design:** a snapshot that is stale or has no date auto-reports **"ask"**, so
the board **cannot show a fabricated date**. Countries you haven't checked simply show "enquire"
(→ WhatsApp) — which is compliant and normal. You do **not** need a real date for every country.

---

## 6. When NOT to sweep (the honest call)

The blanket sweep is a losing trade: most VFS centres show **no slots** (they release in batches),
toggling gets you banned, and the site handles blanks honestly anyway. On 2026-07-29 the whole day
yielded **2 real slots** (Italy, Netherlands) for the cost of 5 rate-limits + an account restriction.

**Default model:**
- Don't run blanket sweeps.
- Check a country's slots **only when a real client is genuinely booking** — one check, individual
  use, real value. Gated operators (TLS/GVCW/embassy) are *always* per-client.
- Leave every other country on **"enquire."**
- If you must top up the board, do **3–4 fresh VFS countries max per session, one check each**, then
  stop for the day.

---

## 7. Compliance guardrails (appointments-specific)

- **Availability shown = indicative, not a live booking system.** The site's disclaimer strip says
  so; keep it. Confirm the real slot with the centre before the client pays.
- **We don't control or guarantee appointment availability** — the centres do.
- **GVCW / individual-use ToS:** never use a client-facing booking platform for bulk/ops checks.
- **No fabricated wait-times.** (An old `lp-appt-board` partial had hardcoded "Updated daily" fake
  waits — flagged for fixing; don't reintroduce that pattern.)

---

## Worked example — 2026-07-29 session

- **Set up + registered:** VFS Netherlands, Austria, Croatia; VFS Italy + Portugal (verified);
  GVCW Greece (`chloe.adams`, book-per-client only). All on `appointments@` + official number.
- **Real slots found:** Italy **06-08** (Edinburgh), Netherlands **17-08** (Edinburgh) → pasted live.
- **Empty:** Austria, Croatia, Portugal.
- **Gated (skipped):** France (needs France-Visas ref), Greece (GVCW individual-only).
- **Rate-limits:** 429201 ×5 (all from sub-category toggling), then **429001 account restriction** →
  sweep parked, 48h reminder set.
- **Lesson:** one check per country, then move on. Everything else = "enquire."
