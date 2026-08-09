# Appointment slot display toggle — 3 modes

Controls how the **public appointment board** (`/schengen-visa-consultancy`) and the
**slot-picker popup** show availability. Two `.env` flags give **3 display modes**. Fully
reversible, no redeploy, no code change.

## ⚠️ Run ONE mode at a time

Set **at most one** of the two flags to `true`. Turning **both** on adds both the `wk` and `cf`
CSS classes to the board section (`sec alt bd wk cf`) whose mobile rules collide (week = left-
aligned header, count = centred), so the layout breaks. Always leave the mode you are not using
`false`.

| # | Mode | flag = true | Board section class |
|---|------|-------------|---------------------|
| 1 | Dates (original) | (none) | `sec alt bd` |
| 2 | Weeks | `UKV_SLOT_WEEK_LABELS` | `sec alt bd wk` |
| 3 | Count / Centre | `UKV_SLOT_COUNT_FOCUS` | `sec alt bd cf` |
| 4 | Neon-glass cards | `UKV_SLOT_ROWS` | `sec alt bd rows` |

Mode 4 (rows/neon-glass): board becomes frosted **neon-glass cards** (2-up, "Light Mist" palette) —
big slot count · country · "Next: {week}" · status chip · band glow edge · WhatsApp "Secure"; each
card opens the same centre popup. Set only ONE
flag true. Verify the class after any change:
`document.querySelector('#appointments').className` should carry exactly one of `wk` / `cf` / `rows`
(or none for dates).

## The flag

`.env` on the host:

```
UKV_SLOT_WEEK_LABELS=true      # week labels  (default, current)
UKV_SLOT_WEEK_LABELS=false     # exact dates  (original behaviour)
```

| Value   | Board card               | Popup chips                                              |
|---------|--------------------------|---------------------------------------------------------|
| `true`  | `Next Week Aug 2026` (bold relative + muted month) | One chip **per week**, holds the soonest slot in that week ("Tap to hold"); exact date confirmed on WhatsApp |
| `false` | `17 Aug 2026`            | One chip **per day** + a time step                      |

Both code paths ship together. `false` reproduces the original date behaviour byte-for-byte.

## Count-focus mode (board only)

Separate flag that makes each board card feature the **slot count** instead of the date/week:

```
UKV_SLOT_COUNT_FOCUS=true      # "Slots open / N / in next 90 days", centred
UKV_SLOT_COUNT_FOCUS=false     # default — show date or week per UKV_SLOT_WEEK_LABELS
```

When `true` it overrides `UKV_SLOT_WEEK_LABELS`. Same clear commands as below. Two effects:

- **Board** card body swaps the date/week for the centred slot count. Card frame/colours/header
  unchanged.
- **Slot popup** becomes a **centre picker** (2x2 tile grid, 1 col on mobile): header "Select your
  centre, {country}". Every centre tile is selectable (radio). The soonest-available centre carries
  a green "Soonest available" chip + "N open" badge and books its soonest slot; centres with no
  published slots carry a "Find me a slot" badge and send a find-a-slot request. No date/week chips.
  Selected tile uses the same band fill as the slot chips (navy / amber / red).

## How to flip it (host)

```
cd /home/outlabio/beyondpassports/ukv-app
# edit .env -> set UKV_SLOT_WEEK_LABELS=true  (or false)
php artisan config:clear && php artisan view:clear && php artisan cache:clear
```

- `config:clear` — reloads the flag.
- `view:clear` — recompiles the board + popup Blade.
- `cache:clear` — the popup preloads a cached slot blob (`appt_modal_payload_v1`, 5-min TTL); clear it or the popup lags up to 5 minutes behind.

No pull/redeploy needed — the flag only reads config.

## Related: timezone

Week math keys off `Carbon::today()`. Keep the app on UK time so the date is correct
(otherwise, near midnight, a UTC server can be a day behind and shift the "This/Next Week"
boundary):

```
APP_TIMEZONE=Europe/London
php artisan config:clear
```

## Week-label logic (for reference)

Boundary is Monday–Sunday, relative to today:

- same week  → `This Week {Mon Year}`
- next week  → `Next Week {Mon Year}`
- 2+ weeks   → `{Nth} Week {Mon Year}`, where Nth = the date's own week-of-month (`ceil(day / 7)`)

Popup rule: **one chip per week, holds the soonest slot** in that week. Two slots in the same
week collapse to a single chip.

## Where it lives in code

- Flag: `config/ukv.php` → `slots.week_labels`
- Label helper: `app/Support/WeekLabel.php` (`for()` / `parts()`)
- Board: `app/Providers/AppServiceProvider.php` (lp-bold composer) + `resources/views/public/lp-bold.blade.php`
- Popup: `app/Services/SlotService.php` (`daysPayload()`), `app/Http/Controllers/AppointmentSlotsController.php`, `resources/views/partials/appt-slot-modal.blade.php`
