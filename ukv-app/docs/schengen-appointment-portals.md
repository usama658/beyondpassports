# Schengen appointment portals — UK applicants (ops reference)

Where to check **live** appointment availability for each Schengen country (UK / London applicants).
Beyond Passports holds **no** own slot inventory for Schengen — appointments are booked on these
external operator portals. This map tells ops where to look; the live availability you record goes
into **Admin → Update availability** (`/admin/update-availability`), which drives the public board
honestly (snapshots expire after 7 days).

> Verify before relying on a row: operators change (e.g. Switzerland moved TLScontact → VFS in
> Jul 2025). All rows below researched against official embassy/operator sources (2026),
> high-confidence, but confirm the live booking link by clicking through from the embassy site.

Stored in-app on each centre (`SupplyNode.contact` = URL, `.notes` = operator) via
`SchengenPortalSeeder`. Re-run after edits: `php artisan db:seed --class=SchengenPortalSeeder`.

## Operator map

| Country | Operator | Booking portal | Note |
|---|---|---|---|
| Austria | VFS Global | https://visa.vfsglobal.com/gbr/en/aut | London/Manchester/Edinburgh |
| Belgium | TLScontact | https://visas-be.tlscontact.com/visa/gb | London + Edinburgh |
| Bulgaria | VFS Global | https://visa.vfsglobal.com/gbr/en/bgr | Long-stay D at embassy; slot scarcity |
| Croatia | VFS Global | https://visa.vfsglobal.com/gbr/en/hrv | Sole partner; slots ~15th monthly |
| Czechia | VFS Global | https://visa.vfsglobal.com/gbr/en/cze | London/Manchester/Edinburgh |
| Denmark | VFS Global | https://visa.vfsglobal.com/gbr/en/dnk | Long UK processing (45–80d) |
| Estonia | VFS Global | https://visa.vfsglobal.com/est/en/gbr | London/Manchester/Edinburgh |
| Finland | VFS Global | https://visa.vfsglobal.com/gbr/en/fin/ | London/Manchester/Edinburgh |
| France | TLScontact | https://visas-fr.tlscontact.com/visa/gb | Apply france-visas.gouv.fr first; London=Wandsworth |
| Germany | **TLScontact** | https://visas-de.tlscontact.com/en-us | NOT VFS. Waiting list from 01.06.2026 |
| Greece | **GVCW** (Global Visa Center World) | https://uk-gr.gvcworld.eu/en | NOT VFS/TLS; centre by postcode |
| Hungary | VFS Global | https://visa.vfsglobal.com/gbr/en/hun | Centre by UK postcode |
| Iceland | VFS Global | https://visa.vfsglobal.com/gbr/en/isl | Pre-register visa.government.is |
| Italy | VFS Global | https://visa.vfsglobal.com/gbr/en/ita | London + Manchester |
| Latvia | VFS Global | https://visa.vfsglobal.com/gbr/en/lva/ | London/Manchester/Edinburgh |
| Liechtenstein | VFS Global (via Switzerland) | https://visa.vfsglobal.com/che/en/gbr/ | Represented by Switzerland |
| Lithuania | VFS Global | https://www.vfsglobal.com/lithuania/uk/ | London/Manchester/Edinburgh/Cardiff |
| Luxembourg | **Embassy direct** | https://londres.mae.lu/en/service_citoyens/visa-immigration.html | No VFS/TLS; book by email |
| Malta | VFS Global | https://visa.vfsglobal.com/gbr/en/mlt/book-an-appointment | Book directly |
| Netherlands | VFS Global | https://visa.vfsglobal.com/gbr/en/nld/book-an-appointment | London/Manchester/Edinburgh/Birmingham |
| Norway | VFS Global | https://visa.vfsglobal.com/gbr/en/nor/book-an-appointment | Apply UDI online first |
| Poland | **Embassy direct (e-Konsulat)** | https://secure.e-konsulat.gov.pl/ | NOT VFS. London consulate closed 13–31 Aug 2026 |
| Portugal | VFS Global | https://visa.vfsglobal.com/gbr/en/prt/ | Slots released weekly (Thu) |
| Romania | **Embassy direct (eViza)** | https://eviza.mae.ro/ | NOT VFS; online eViza then consulate appt |
| Slovakia | **Embassy direct** | https://ezov.mzv.sk | e-application then email for appointment |
| Slovenia | VFS Global | https://visa.vfsglobal.com/gbr/en/svn | London/Manchester/Edinburgh |
| Spain | **BLS International** | https://uk.blsspainvisa.com/london/ | Sole partner since Oct 2023; London + Manchester |
| Sweden | VFS Global | https://visa.vfsglobal.com/gbr/en/swe/ | London + Edinburgh; EUR20 VFS fee |
| Switzerland | TLScontact | https://ch.tlscontact.com/gb/lon/index.php | London/Manchester/Edinburgh |

Watch the non-VFS exceptions: Germany & France & Belgium & Switzerland (TLScontact), Greece (GVCW),
Spain (BLS), and the four embassy-direct ones (Luxembourg, Poland, Romania, Slovakia).

## Weekly availability routine

1. Open each portal above, read the soonest bookable appointment.
2. In **Admin → Update availability**, paste lines: `spain: 2026-07-14 good`, `france: limited`,
   `italy: ask` (unknown / none free).
3. Save. The public board + finder show real state; entries auto-expire in 7 days, so re-check
   weekly. `staleCentres()` flags anything missing/expiring.

Bands: `good` = plenty free soon · `limited` = few/further out · `ask`/blank = none or unknown
(board shows "contact us").

## Portal account setup checklist

No public APIs — every check/booking is manual and logged in. CAPTCHAs block automation.

### How many emails? → One mailbox + a catch-all (unlimited addresses)

Do NOT use client emails (too slow) and do NOT rely on `+aliases` (portals reject them). Instead set
up a **catch-all** on a dedicated subdomain so you get unlimited *real, distinct* addresses that all
land in one inbox.

- **1 operator inbox** — `appointments@beyondpassports.co.uk` — for the ~7 operator/agent accounts
  + general notifications.
- **1 catch-all subdomain** — e.g. `@bk.beyondpassports.co.uk` (or `@apply...`). Every address on it
  (`anything@bk.beyondpassports.co.uk`) auto-routes to one mailbox. Gives a unique address per
  booking with zero per-mailbox cost and no alias rejection.
- **One unique address per booking**, encoding the order ref for traceability:
  `ord-UKV-2026-100245@bk.beyondpassports.co.uk`. Use it for that applicant's portal account; the
  confirmation lands in the shared catch-all inbox.

So: **1 operator inbox + 1 catch-all subdomain = unlimited booking emails**, no client emails needed.

**Setup (cPanel):** Email → Default Address (or Domains → add subdomain `bk`, then set its default
address) → "Forward to / deliver to" your ops mailbox. Use a **subdomain**, not the main domain, so
catch-all spam stays off `@beyondpassports.co.uk`. Keep all portal passwords in a password manager,
keyed by the booking address.

GDPR note: still your processing — record consent/LOA per order as before; the email mechanism
doesn't change that.

### Accounts to register (≈7 + 1 email-only)
For each: register with the ops email, set a strong password (password manager — never in repo/chat),
add phone, enable 2FA if offered, and **ask whether an agent / business / corporate account exists**
(lets one login manage many applicants — biggest time-saver).

- [ ] **VFS Global** — one account covers most countries (Austria, Bulgaria, Croatia, Czechia,
  Denmark, Estonia, Finland, Hungary, Iceland, Italy, Latvia, Liechtenstein, Lithuania, Malta,
  Netherlands, Norway, Portugal, Slovenia, Sweden). Ask VFS for corporate/agent access.
- [ ] **TLScontact** — Germany, France, Belgium, Switzerland. Supports group/family applications.
  France needs a france-visas.gouv.fr reference first; Germany has a waiting-list queue (from 01.06.2026).
- [ ] **BLS International** — Spain (uk.blsspainglobal.com). Appointment-only, cashless.
- [ ] **GVCW** — Greece (uk-gr.gvcworld.eu).
- [ ] **e-Konsulat** — Poland (govt portal). London consulate closed 13–31 Aug 2026.
- [ ] **eViza** — Romania (govt portal).
- [ ] **ezov.mzv.sk** — Slovakia (govt e-application, then email for appointment).
- [ ] **Luxembourg** — no portal: email the consulate to book (`Londres.consulat@mae.etat.lu`).

### Before booking for a client
- [ ] Signed letter of authority / consent to act + to enter their data (GDPR — you're a processor).
- [ ] Applicant details ready (name, passport no., travel dates) — slots usually appear only after
  you start an application and pick country + centre + visa type.
- [ ] Record the operator booking reference on the order (Admin → Appointments).
