# Appointment slot display toggle — weeks vs dates

Controls how the **public appointment board** (`/schengen-visa-consultancy`) and the
**slot-picker popup** show availability. One switch drives both surfaces. Fully reversible,
no redeploy, no code change.

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
- **Slot popup** becomes a **centre picker**: header "Select your centre, {country}", each centre
  is a selectable row (radio), the soonest-available centre carries a green "Soonest available"
  chip + "N open" badge, empty centres show "we check live for you". Picking a centre books its
  soonest slot on WhatsApp — no date/week chips. Selected row uses the same band fill as the slot
  chips (navy / amber / red).

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
