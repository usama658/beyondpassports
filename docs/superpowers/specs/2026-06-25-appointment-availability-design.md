# Appointment-availability data infrastructure — design

Date: 2026-06-25
Status: approved (brainstorm) → implementation

## Goal

Make the `/destinations` appointment board light up **honestly**, fed by a semi-automatic,
manually-owned data source (no scraping). Today every Schengen country shows "Ask us / On
request" because no supply node is linked to a Schengen destination and there is no published
availability record. This builds the data layer + ops workflow + board rewire so real
availability can appear, decays automatically when stale, and never shows fabricated data.

Standing constraint: never show fake availability (DMCCA 2024). The board always carries
"Indicative only. We confirm live availability with the centre before you pay." Anything
past its freshness window reverts to "Ask us" automatically.

## Decisions (locked in brainstorm)

- Data source: **ops enter manually** (no scraping).
- Granularity: **per-centre availability snapshot** (next-available date + band), not individual slots.
- Semi-automatic = all four: auto-expiry+reminders, bulk paste/import, derive-from-bookings, smart prefill.
- Storage: **new `centre_availability` table** (Approach A) — keep `centre_slots` for real order-hold inventory.
- Scope: **one bookable centre per Schengen country** to start (expandable to multi-city later).

## Section 1 — Data model

Migration `create_centre_availability_table`:

| column | type | notes |
|---|---|---|
| id | pk | |
| supply_node_id | foreignId, unique, cascadeOnDelete | one snapshot per centre |
| next_available_on | date, nullable | null = none known |
| band | string(10), nullable | `good` / `limited` (null when no date) |
| source | string(10), default `manual` | `manual` / `derived` |
| note | string, nullable | internal |
| confirmed_at | datetime | last set/refresh ("as of") |
| expires_at | datetime | confirmed_at + freshness window (default 7 days) |
| timestamps | | |

`CentreAvailability` model:
- `belongsTo SupplyNode`; `SupplyNode hasOne availability()`.
- `fillable` all data columns; casts dates/datetimes.
- **Status accessor `status()` is the single source of truth:** if `expires_at` past OR `next_available_on` null → `ask`; elseif `band==good` → `ok`; elseif `band==limited` → `lim`; else `ask`.
- Helper `isStale()` (expired) and `isExpiring(int $days=2)`.
- `band` allowed values guarded in app (enum-like constant array on the model).

## Section 2 — Seeder

`SchengenCentreSeeder` (registered in `ProductionSeeder` so it runs on deploy; also `DatabaseSeeder` for local):
- One `centre` node per Schengen country (29), `updateOrCreate` on `node_key` (`schengen-centre-{slug}`), re-run safe.
- `type=centre`, `we_book_here=true`, `is_global=false`, `name="{Country} visa application centre — London"`, real provider (TLScontact/VFS/BLS) in `notes` where confidently known, London hub `postcode`/`lat`/`lng`.
- Links node to its `Destination` via `supplyNodes()` pivot (`syncWithoutDetaching`).
- Creates **no** `centre_availability` rows → board stays honest-empty until real data set.
- Provider/address flagged (note) as "verify before relying" — covers pending #95.

## Section 3 — AvailabilityService + board rewire

`AvailabilityService::byDestination(string $visaType='Schengen'): array` (keyed by destination id):
- Load Schengen destinations with `supplyNodes.availability`; include global bookable centres' snapshots.
- Per destination: from non-expired snapshots of its centres (+ global), compute
  `next_available_on` = soonest date; `status` = best band (`ok` > `lim`), else `ask`;
  `confirmed_at` = freshest stamp (drives "as of {date}" on tile).
- `DestinationController::index()` calls `AvailabilityService` instead of `SlotService::availabilityByDestination()`.
- Remove `SlotService::availabilityByDestination()` (conflated marketing with order inventory); `SlotService` order-hold methods untouched.
- Blade tile: add small "as of {date}" stamp when a real snapshot exists; else "On request / We check live for you" as now. Tab UI + honesty note + legend unchanged.

## Section 4 — Semi-auto features + Filament

1. **Auto-expiry/decay** — read-time only (status accessor). Optional nightly `availability:sweep` command nulls long-dead rows for tidiness + logs expirations. No write needed for the revert.
2. **Derive from bookings** — `availability:derive` scheduled command: reads recent confirmed appointment dates on orders (Phase 5 `appointment_at`), writes `source=derived` snapshot for that order's destination centre **only if** no fresher `manual` snapshot exists. Manual always wins. Surfaced with the standard "indicative, confirmed live" caveat.
3. **Bulk paste/import** — Filament page "Update availability": textarea, lines `france: 2026-06-12 good` or `france: ask`; parser upserts snapshots (`confirmed_at=now`, `expires_at=+window`, `source=manual`); preview + validation report before save.
4. **Smart prefill** — same page pre-loads current values for all centres, flags expired / expiring-≤2-days rows so ops touch only what changed.
- Per-row `CentreAvailabilityResource` for one-off edits.
- Owner daily digest (#94) gains "N centres stale/expiring" line.

## Section 5 — Testing

Pest:
- Model: status/decay (expired→ask, null date→ask, band→ok/lim), isStale/isExpiring.
- `AvailabilityService`: multi-centre soonest + best-band, global centre, all-expired→ask, manual>derived.
- Seeder: idempotent, 29 centres, 29 links, no availability rows created.
- Bulk-paste parser: valid/invalid lines, `ask` reset, bad date/band rejected.
- Derive command: manual-wins, no-overwrite-fresher, writes derived when empty.
Then: `view:cache`, curl board, local seed snapshot proving lit-up, deploy, poll live.

## Out of scope (later)

- Multi-city centres per country (start one each).
- Public-facing scraping/automation.
- Showing provider names publicly (internal only for now).
