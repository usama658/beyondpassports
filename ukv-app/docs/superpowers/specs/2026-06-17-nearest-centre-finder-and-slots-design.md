# Nearest-centre finder + held-slot inventory — design spec

**Goal:** show users the nearest in-person centres (IDP/PayPoint, visa application centres) to their location so they don't have to search, highlight centres where UKVisaCo holds appointment slots, and (Phase 2) show live held-slot availability near them.

**Decisions (locked):** location = UK postcode + optional one-tap browser geolocation; geocoding via free postcodes.io (no key) + Haversine distance; surfaces = dedicated `/find-a-centre` page + a reusable `nearest-centre` partial embedded on the IDP page (`driving-abroad`) and the checklist result page.

**Phasing:** Wave 1 = foundation finder (geo + nearest + "we book here" badge). Wave 2 = held-slot inventory (centre_slots + ops tooling + availability display + hold-on-apply). Build both now; Wave 2 plugs into Wave 1's surface.

**Caveats:** renders empty until real centre geo + slots are populated (owner, #95) and postcodes.io is reachable. Live inventory is only meaningful if UKVisaCo actually holds slots (operational).

## Wave 1 — foundation

### Schema / model (A1)
- `supply_nodes` add: `address` (string null), `postcode` (string null), `lat` (decimal 9,6 null), `lng` (decimal 9,6 null), `we_book_here` (bool default false).
- `SupplyNode` model: add to fillable + casts (lat/lng decimal, we_book_here bool). Scope `scopeLocated` (whereNotNull lat & lng).
- `GeoService::haversineKm(float $lat1,$lng1,$lat2,$lng2): float`.

### Geocoding + finder (A2)
- `PostcodeService::lookup(string $postcode): ?array{lat:float,lng:float}` — GET `https://api.postcodes.io/postcodes/{pc}`; guarded (HTTP fail/404 → null); cached (Cache::remember, 1 day); never throws.
- `CentreFinderService::nearest(float $lat, float $lng, ?string $type = null, int $limit = 5): \Illuminate\Support\Collection` — located nodes only, optional type filter (centre|paypoint|embassy|courier), each item `['node'=>SupplyNode,'distance_km'=>float]`, sorted ascending. `we_book_here` nodes get a small rank boost on ties.

### Public surface (A3)
- `CentreController::page(): View` — GET `/find-a-centre` (postcode box + optional "use my location" JS + type filter).
- `CentreController::search(Request): View` — GET `/find-a-centre/search?postcode=` OR `?lat=&lng=` → resolves to lat/lng (PostcodeService for postcode), runs CentreFinderService, renders results. No-JS works (postcode form GET).
- `resources/views/public/find-a-centre.blade.php` + `resources/views/partials/nearest-centre.blade.php` (reusable: takes `$results`). Results card includes `@include('partials.centre-slots', ['node'=>..., 'slots'=>...])` (created by B3) for availability — guarded so it renders nothing in Wave-1-only state.
- Compliance: not-a-government-site; PayPoint locations link the official PayPoint locator where applicable (we don't replicate their full DB).

### Admin (A4)
- Extend `SupplyNodeResource`: address, postcode, lat, lng, `we_book_here` toggle; a "Geocode from postcode" action (calls PostcodeService, fills lat/lng).

## Wave 2 — held-slot inventory

### Schema / model (B1)
- `centre_slots`: `supply_node_id` FK (cascade), `slot_at` (datetime), `status` (string default 'available' — available|held|booked), `hold_expires_at` (datetime null), `order_id` FK null, timestamps. Index (supply_node_id, status, slot_at).
- `CentreSlot` model + belongsTo SupplyNode/Order; scopes available(), heldExpired().
- `SlotService::nextAvailableNear(float $lat,$lng,?string $type,int $limit): Collection` (centre + its next available slot, by distance); `hold(CentreSlot,$order,$minutes): bool` (status=held + expiry, guarded against double-hold); `releaseExpired(): int`.

### Admin (B2)
- `CentreSlotResource` (+pages): ops add/bulk-add slots per centre, set status; filter by centre/status/date.

### Availability surface + hold (B3)
- `resources/views/partials/centre-slots.blade.php` — presentational; given a node + its available slots, shows "Next available: {date}" + a count, or nothing when none. Included by A3's results card.
- Hold-on-apply hook + `slots:release-expired` command (scheduled). Hooks read SlotService.

## Integration (parent, not an agent)
Routes (`/find-a-centre`, `/find-a-centre/search`), nav link, sitemap entry, `slots:release-expired` schedule. Migrate, full suite, commit.

## Testing
Haversine correctness; PostcodeService guarded no-op + caching; nearest ordering + type filter + we_book_here boost; finder page/search 200 incl. no-JS postcode; slot available/hold/expire; release command. Empty-data renders gracefully everywhere.
