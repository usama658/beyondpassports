# Appointment Booking Data Scraping — Trello task list

Operational task list for the manual, ToS-compliant availability checking.
Full method + rules: `appointment-availability-playbook.md`.

## Per-session card — "Availability sweep — [date]"

- [ ] Pick 3–4 **sweepable** countries (VFS/BLS only; check `Sweepable` column). Skip freshly-done
      (Italy/Netherlands) and all gated (France/Greece/Belgium/CH/Poland/Slovakia/Romania/Lux).
- [ ] Confirm the VFS account is **not 429001-restricted** before starting.
- [ ] Per country: open its portal URL → log in (`appointments@` + row password) → Book Appointment
      → **one** centre + **one** category → read the earliest slot.
- [ ] **⚠️ ONE check per country — never toggle category/centre** (trips 429201 → 429001 ban).
- [ ] Log to **Slot Detail** tab (Country · Centre · Category · Soonest · Checked).
- [ ] Update **Schengen Availability** tab (rolled-up soonest + band + checked date).
- [ ] Update **Portal Accounts** (status/notes).
- [ ] Paste block → **Admin ▸ Update Availability** (`slug: YYYY-MM-DD good|limited`).
- [ ] Stop after 3–4 countries.

## Recurring / maintenance

- [ ] Refresh live slots weekly (the ones we hold: Italy, Netherlands).
- [ ] Per-client check — when a real client books, check *their* country (one genuine check = fine).
- [ ] Verify stale snapshots auto-flip to "enquire" (board self-corrects).

## Status / blockers

- [ ] VFS **429001** restriction — retry 31 Jul+, one careful check (reminder set).
- [ ] France TLS — gated (France-Visas ref); per client only.
- [ ] Greece GVCW — individual-use ToS; per client only.
- [ ] Rotate `appointments@` password (exposed in ops).

## Done

- [x] Accounts registered: NL, AT, HR, IT, PT (VFS) + Greece (GVCW).
- [x] Slots captured: Italy 06-08, Netherlands 17-08.
- [x] 3-tab tracker + playbook shipped (02cc1e6).

**Labels:** VFS · Gated · Blocked · Live-data. **Golden rule on every card:** one category check per
country, then stop.
