# Per-market availability boards (snapshot-only) design spec

**Status:** draft for owner review. Written 2026-10-06. Sub-project 4 of 7 in the international + tours programme.
**Decision basis:** `docs/product-goals-2026-10.md` (G1, G3) and `docs/superpowers/research/2026-10-06-README-decision-basis.md`. Every decision below names the file or finding it rests on.
**Depends on:** SP1 `2026-10-06-international-market-foundation-design.md` (Market object, `market_url`, `/{market}/schengen-visa` hub stub, `MarketHubController::SCHENGEN`) and the merged SA phase 1 (`2026-09-05-south-africa-market-design.md`: `supply_nodes.market`, `AvailabilityService($market)`, Filament market selector). Nothing here is buildable until the SP1 plan is merged.
**Supersedes:** the hub stub's static "We'll check for you" block from SP1 Task 6 (kept as the not-yet-live state, see 4.6).

## How to use this document
1. Read sections 1-3. If the goal, scope or an assumption is wrong, say so now.
2. For each decision in sections 4-8 ask: does it move G1's scoreboard line ("live board with real 'last checked' stamp in every launched market") and does the cited evidence support it?
3. Approve by section. "Approved" on the whole document unlocks the plan `2026-10-06-sp4-market-availability.md`.
4. After build, section 10 is the acceptance checklist.
5. To change something later: edit this file, then the plan, then the code.

## 1. Goal
Give every `.com` market a per-market availability board fed ONLY by real, timestamped ops snapshots, with a named checker and the official free booking link on every row, so the board is the honesty wedge G1 describes ("we watch, you book, we prepare") and not a second copy of the UK's simulated pool.
Success: `/{market}/schengen-visa` renders the board when at least one snapshot row exists for that market; renders the SP1 "We'll check for you" block otherwise; and no `.com` route can reach `App\Support\SlotBoard` or `App\Support\SlotTiles` (hard guard + test). Scoreboard lines: G1 target ("live board with real 'last checked' stamp in every launched market"), G3 ("nobody exposes live availability as crawlable text").

## 2. Scope
In: data columns for operator / booking mode / official link / checker name / portal state / last-seen; `AvailabilityService` paste tokens; `UpdateAvailability` checker + booking-mode fields; `App\Support\MarketBoard` renderer + `partials.market-board`; hub integration; honest per-operator copy from the market deep dives; SlotBoard/SlotTiles hard guard; stage gate; Filament fields on `SupplyNodeResource`; tests; runbook/playbook notes.
Out: any automated polling or scraping (playbook §0, §6: human-paced, one check per country); non-UK VAC accounts (SP6, feasibility premise check 2); SP3 destination page templates (they CONSUME `MarketBoard::rows($market, $destination)` and are specified there); UK board changes beyond additive, behaviour-preserving columns; alert messaging automation (WhatsApp templates stay manual, appointment lane §7).

## 3. Assumptions standing in for open decisions
- A1 No non-UK VAC account exists today (feasibility premise 2; playbook §0b). Effect: the stage gate in 4.6 is enforced in code (board live only when a snapshot row exists), and ops create supply nodes per market by hand in Filament once a real check has been logged in the Portal Accounts tab. No seed data for non-UK centres ships in code (operator matrix is 116 cells, 2 verified: feasibility gap 4).
- A2 One ops identity for now (feasibility "Open question"). Effect: `checked_by` is a free-text first name defaulting to the logged-in Filament user's name; the board prints it as "by {name}".
- A3 UK board semantics must not change in this sub-project (G6: fix the UK auction first; no UK copy edits here). Effect: new columns are additive with defaults; `status()` maps the new `filling` band to the existing `lim`; the UK board never reads `portal_state`, `checked_by` or `booking_mode`.
- A4 Timezone per market is display-only (`Africa/Johannesburg`, `Asia/Dubai`, `America/New_York`, `America/Toronto`); timestamps are stored in app time as today.

## 4. Decisions
### 4.1 Data model (additive, defaults preserve UK)
`supply_nodes` gains: `operator` string(20) nullable (`vfs|tlscontact|bls|capago|gvcw|prenotami|consulate|email_queue`), `booking_mode` string(20) default `calendar` (`calendar|waitlist|allocation|embassy|email`), `booking_url` string(255) nullable (the official free booking page). Operators differ per market for the same destination (service brief §11: SA Switzerland = VFS not TLS; CA Spain = consulate email queue; US Germany = BLS), so these live on the per-market node, not on `destinations`.
`centre_availability` gains: `checked_by` string(60) nullable; `portal_state` string(12) default `ok` (`ok|unavailable|unknown`); `last_seen_at` datetime nullable (carried forward: set to `confirmed_at` whenever a dated snapshot is written, preserved when a dateless one is written). This is Visa Catcher's "last seen X ago" device (appointment lane §4: "strongest honesty device observed").
`CentreAvailability::BANDS` becomes `good|filling|limited`; `status()` maps `filling` to `lim` so the UK board is unchanged (A3). `hero-urgency-config.md` enum A already has `filling`; the board reuses its vocabulary.

### 4.2 Paste-block tokens (backwards compatible)
`AvailabilityService::parseBulk($input, $market)` accepts, per line:
```
<slug>: <YYYY-MM-DD> <good|filling|limited> [mode]
<slug>: none [mode]      checked, no dates seen (fresh snapshot, null date, portal ok)
<slug>: down             portal unavailable / login blocked (fresh snapshot, portal unavailable)
<slug>: ask              reset to unknown (portal_state unknown) = "We'll check for you"
```
`mode` is an optional trailing token from `calendar|waitlist|allocation|embassy|email` and updates the node's `booking_mode` on apply. A line like `germany: 2026-11-02 waitlist` (mode token where the band should be) is an error row, never a silent write. Existing UK lines parse exactly as before. `setSnapshot()` gains named params `checkedBy` and `portalState`.
Source: playbook §4 bands + §5 paste format; appointment lane §7 states list.

### 4.3 Hard guard: the simulated pool is unreachable from `.com`
`SlotBoard::allocation() / remaining() / remainingFor() / total() / nextDate()` and `SlotTiles::hydrate()` throw `LogicException` when `Market::current()` is not UK. `SlotBoard::recordInquiry()` and `decrement()` already swallow Throwables, so a `.com` lead can never fail because of the guard (test). A second, static test asserts that `app/Support/MarketBoard.php`, `app/Http/Controllers/Market/*.php`, `resources/views/market/*.blade.php` and `resources/views/partials/market-board.blade.php` contain neither `SlotBoard` nor `SlotTiles`. A third test turns `ukv.slots.dynamic` on and asserts the market hub renders no `slots open` / `data-slot-count` markup.
Source: appointment lane §7 FLAG ("the simulated 70/week dynamic pool contradicts G1's honesty wedge; switch .com boards to snapshot-only, keep dynamic OFF on .com"); `docs/appointment-slots-dynamic.md` compliance note (DMCCA 2024 fake-scarcity risk, owner proceeded for UK only); SP1 spec §6.

### 4.4 `App\Support\MarketBoard` renderer
`MarketBoard::isLive(Market $m): bool` (any `centre_availability` row whose node has `market = code`).
`MarketBoard::rows(Market $m, ?Destination $only = null): array` returns one row per (destination, centre node) in that market, ordered by destination then centre. Row keys: `destination, slug, operator, operator_code, centre, booking_mode, state, state_label, earliest, last_checked, checked_by, last_seen, booking_url`.
`MarketBoard::stateFor(SupplyNode $node, ?CentreAvailability $a): string`:
```
no snapshot, or isStale(), or portal_state = unknown         -> check       "We'll check for you"
portal_state = unavailable                                  -> portal_down "Portal unavailable"
no date and booking_mode = allocation                       -> allocation  "Allocation queue"
no date and booking_mode = waitlist                         -> waitlist    "Waitlist"
no date (calendar / embassy / email)                        -> none        "No dates seen" + "last seen DD Mon" when last_seen_at
date + band good / filling / limited                        -> available / filling / limited
```
Stale means `expires_at` passed (7 days, `CentreAvailability::FRESHNESS_DAYS`) or the date slipped into the past (existing `isStale()`). Exact date is shown whenever a row is not stale, so a band-only fallback is never needed. `last_checked` is `confirmed_at` in the market timezone plus `by {checked_by}`.
`MarketBoard::copyFor(Market $m, array $rows): array` returns the global honesty line plus one line per operator code PRESENT in the rows, from `config('ukv.board_copy')`. Operators without a node render no copy, which is how "France AE: VERIFY OPERATOR before publishing" (UAE deep dive) and "Greece: do not publish until checked" (US+CA deep dive) are enforced.
Columns match the locked brief: Destination | Operator | Centre | Booking mode | Earliest date seen | Last checked (timestamp + checker) | Official free booking link (service brief §2; appointment lane §7 board spec).

### 4.5 Blade partial and copy
`partials.market-board` renders the table, the state pills, the per-operator copy block, the "Destinations not yet on this board: we'll check for you on WhatsApp" line, and the WhatsApp CTA with the `[CODE]` tag. Global line (verbatim from appointment lane §7): "Checked by a person, not a bot. Dates move within minutes; a date shown here can be gone before you log in. We never hold or sell appointments." Each official link is labelled "Book free on the official site"; when `booking_url` is null the operator name renders without a link (never a placeholder URL).
Per-operator lines are copied from the market deep dives ("Appointment copy per operator" sections) with three edits: no em-dashes, no "fast-track" / "guaranteed" / "priority appointment" / "early appointment" substrings even in negation (SP1 hub test bans them), and "Prime Time is a comfort slot, not a faster decision" kept because TLScontact says it in writing.

### 4.6 Stage gate (code-enforced)
A market board goes live only when `MarketBoard::isLive($market)` is true. Until then the hub renders the SP1 "Appointment availability: we will check for you" block unchanged. The first snapshot row can only be created through `UpdateAvailability` with a checker name, which is the "one logged VAC check per core destination" gate from product goals (Launch order + gates) made mechanical. The playbook §0b step (first real tenant check, log error codes in Portal Accounts) stays the human half of the same gate.

### 4.7 Ops surface
`UpdateAvailability`: market Select options come from `['uk'] + Market::codes()` with labels; new required `checker` TextInput defaulting to the logged-in user's name; prefill block emits the new tokens (`none`, `down`, mode suffix) so round-tripping works; apply writes `checked_by` and `portal_state` per row and updates `booking_mode` when a mode token is present.
`SupplyNodeResource` form gains `market` Select, `operator` Select, `booking_mode` Select, `booking_url` TextInput (url). `CentreAvailabilityResource` table gains `checked_by` and `portal_state` columns. No new Filament page.

### 4.8 SP3 contract
SP3 destination pages call `MarketBoard::isLive($market)` and `MarketBoard::rows($market, $destination)` and include `partials.market-board` with those rows. Nothing else is exposed; SP3 must not query `CentreAvailability` directly.

## 5. Error handling
Unknown operator code on a node: operator column prints "Operator pending verification". Null `booking_url`: no link. Null `checked_by` on an old row: "Last checked {time}" without "by". Snapshot with a past date: `isStale()` true, state `check`. Mode token without band: error row, nothing written. Any exception inside `MarketBoard::rows` is not caught: a broken board must fail tests, not render blanks.

## 6. Analytics
No new events. The hub already pushes `bp_market` (SP1 §8). Row clicks on official links use `rel="noopener nofollow"` and `target="_blank"`; no outbound tracking in this sub-project.

## 7. SEO
Board rows are server-rendered text inside the hub (G3: "nobody exposes live availability as crawlable text"). No schema change here; SP3 wraps rows in `Service` / `dateModified` schema.

## 8. Config
`config/ukv.php`: `markets.<code>.timezone`; new key `board_copy` = `['_all' => string, 'za' => [operator => line], 'ae' => [...], 'us' => [...], 'ca' => [...]]`.

## 9. Not doing (and why)
No "Filling" computed from date distance (would be a guess; band is set by a human from what the calendar showed). No per-destination aggregation on `.com` (centre-level rows are the G3 wedge: "centre-level appointment pages with live text"). No automatic alerts. No seeded centres.

## 10. Testing (acceptance checklist)
`php artisan test --filter='MarketBoard|AvailabilityServiceMarketBoard|UpdateAvailabilityMarketBoard|MarketHubBoard|CentreAvailabilityState'`:
1. BANDS include `filling`; `status()` maps it to `lim`; `PORTAL_STATES` enforced in `setSnapshot`.
2. `setSnapshot` writes `checked_by` + `portal_state`; `last_seen_at` set on dated writes and carried forward on dateless writes.
3. `parseBulk` tokens: dated+mode, `none`, `down`, `ask`, mode-in-band-position error, `filling` band; UK lines unchanged.
4. SlotBoard / SlotTiles throw `LogicException` for a bound non-UK market; `recordInquiry` does not throw; static grep test passes.
5. `MarketBoard::stateFor` covers all eight states incl. stale-by-past-date.
6. `MarketBoard::isLive` false on empty market, true after one snapshot; `rows()` ordering and `$only` filter.
7. `copyFor` returns global line + only present operators; no banned substrings; no em-dash.
8. Hub: check block when not live; table when live with "Last checked ... by Chloe", "last seen DD Mon", official link, state labels; `ukv.slots.dynamic=true` leaves the hub free of pool markup.
9. `UpdateAvailability::applyBulk` writes checker + portal state, updates booking_mode; prefill emits tokens.
Existing suites green: `AvailabilityServiceTest`, `UpdateAvailabilityPageTest`, `SlotBoardTest`, `SlotTilesTest`, `MarketHubPageTest`, `Lp*`.

## 11. Competitor citation per decision
| Decision | Beats / learns from | Source |
|---|---|---|
| Snapshot-only, simulated pool unreachable on .com | Visard "Book in 1 Week" headline vs Visa Catcher "last seen X ago"; Atlys "indicative, best effort" | appointment lane §3, §4, §7 FLAG; slots-dynamic compliance note |
| Timestamp + checker name per row | Visa Catcher "checked 24s ago" (bot); nobody pairs a board with a named human | appointment lane §4 "Nobody pairs a board with a named human"; G1 |
| "last seen DD Mon" on empty rows | Visa Catcher empty-row device | appointment lane §4 |
| Booking mode column (calendar / waitlist / allocation / embassy / email) | TLS France UK allocation since 1 Aug 2026; Spain Toronto email queue; Germany AE waitlist | appointment lane §3, §6 item 5; service brief §2, §11 |
| Official free booking link on every row | VFS "Appointments are free of cost and can be booked only on vfsglobal.com"; Italy Toronto "it is a scam" | appointment lane §3; US+CA deep dive trust lines |
| Centre-level rows, not country aggregates | nobody has centre-level live text | G3 gap; targeting map "centre-level appointment pages" |
| Operator copy only when operator verified | AE France TLS vs VFS conflict; Greece unverified in all four | UAE deep dive table; service brief §12 |
| Stage gate = first snapshot row | "board would render 'enquire' x29 = visibly empty, not a wedge" | feasibility premise 2, gap 2 |
| Human-paced checks only | VFS 429201/429001; bartug tracker "VFS terms don't permit automated access" | playbook §0; appointment lane §1 |

## 12. Open items carried forward
SP3: destination template consumes `MarketBoard::rows($market, $destination)`. SP6: VAC accounts per market; second ops identity; `checked_by` becomes a user reference once more than one checker exists. SP7: Clarity segment on board row clicks.
