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
