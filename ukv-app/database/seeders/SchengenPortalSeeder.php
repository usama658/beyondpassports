<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupplyNode;
use Illuminate\Database\Seeder;

/**
 * Real appointment-portal map for Schengen application centres (UK applicants, 2026).
 *
 * Stamps each schengen-centre-* SupplyNode with the genuine operator + applicant-facing booking
 * URL so ops know WHERE to check live availability (the app holds no own Schengen inventory —
 * appointments are booked on these external portals). Researched against official embassy/operator
 * sources; all rows high-confidence but the live booking link should be re-confirmed periodically
 * (operators change — e.g. Switzerland moved TLScontact -> VFS in Jul 2025).
 *
 * Idempotent: matches on node_key, updates contact (URL) + notes (operator + note). Safe to re-run.
 */
class SchengenPortalSeeder extends Seeder
{
    /** @var array<string, array{0:string,1:string,2:string}> slug => [operator, bookingUrl, note] */
    private const MAP = [
        'austria'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/aut', 'London/Manchester/Edinburgh.'],
        'belgium'       => ['TLScontact', 'https://visas-be.tlscontact.com/visa/gb', 'London + Edinburgh.'],
        'bulgaria'      => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/bgr', 'Long-stay D lodged at embassy. Slot scarcity reported.'],
        'croatia'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/hrv', 'VFS sole partner. Slots open ~15th monthly.'],
        'czechia'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/cze', 'London/Manchester/Edinburgh.'],
        'denmark'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/dnk', 'Long UK processing (45-80d).'],
        'estonia'       => ['VFS Global', 'https://visa.vfsglobal.com/est/en/gbr', 'London/Manchester/Edinburgh.'],
        'finland'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/fin/', 'London/Manchester/Edinburgh.'],
        'france'        => ['TLScontact', 'https://visas-fr.tlscontact.com/visa/gb', 'Apply france-visas.gouv.fr first. London = Wandsworth.'],
        'germany'       => ['TLScontact', 'https://visas-de.tlscontact.com/en-us', 'NOT VFS. Waiting list from 01.06.2026.'],
        'greece'        => ['Global Visa Center World (GVCW)', 'https://uk-gr.gvcworld.eu/en', 'NOT VFS/TLS. Centre by postcode.'],
        'hungary'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/hun', 'Centre allocated by UK postcode.'],
        'iceland'       => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/isl', 'Pre-register visa.government.is; decided by Iceland embassy.'],
        'italy'         => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/ita', 'London + Manchester.'],
        'latvia'        => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/lva/', 'London/Manchester/Edinburgh.'],
        'liechtenstein' => ['VFS Global (via Switzerland)', 'https://visa.vfsglobal.com/che/en/gbr/', 'Represented by Switzerland. Swiss switched TLS->VFS Jul 2025.'],
        'lithuania'     => ['VFS Global', 'https://www.vfsglobal.com/lithuania/uk/', 'London/Manchester/Edinburgh/Cardiff.'],
        'luxembourg'    => ['Embassy direct', 'https://londres.mae.lu/en/service_citoyens/visa-immigration.html', 'No VFS/TLS. Book by email, appointment only.'],
        'malta'         => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/mlt/book-an-appointment', 'Book directly.'],
        'netherlands'   => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/nld/book-an-appointment', 'London/Manchester/Edinburgh/Birmingham.'],
        'norway'        => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/nor/book-an-appointment', 'Apply UDI online first, then biometrics at VFS.'],
        'poland'        => ['Embassy direct (e-Konsulat)', 'https://secure.e-konsulat.gov.pl/', 'NOT VFS. London consulate closed 13-31 Aug 2026.'],
        'portugal'      => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/prt/', 'Slots released weekly (Thu).'],
        'romania'       => ['Embassy direct (eViza)', 'https://eviza.mae.ro/', 'NOT VFS. Online eViza then consulate appointment.'],
        'slovakia'      => ['Embassy direct', 'https://ezov.mzv.sk', 'NOT outsourced. e-application then email for appointment.'],
        'slovenia'      => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/svn', 'London/Manchester/Edinburgh; or embassy direct.'],
        'spain'         => ['BLS International', 'https://uk.blsspainvisa.com/london/', 'Sole partner since Oct 2023. London + Manchester.'],
        'sweden'        => ['VFS Global', 'https://visa.vfsglobal.com/gbr/en/swe/', 'London + Edinburgh. EUR20 VFS fee.'],
        'switzerland'   => ['TLScontact', 'https://ch.tlscontact.com/gb/lon/index.php', 'London/Manchester/Edinburgh.'],
    ];

    public function run(): void
    {
        $updated = 0;
        foreach (self::MAP as $slug => [$operator, $url, $note]) {
            // Match the country's London node AND any city variants (schengen-centre-{slug}[-city]).
            $nodes = SupplyNode::where('node_key', 'schengen-centre-'.$slug)
                ->orWhere('node_key', 'like', 'schengen-centre-'.$slug.'-%')
                ->get();
            foreach ($nodes as $node) {
                $node->contact = $url;
                $node->notes = 'Operator: '.$operator.' | '.$note;
                $node->save();
                $updated++;
            }
        }

        $this->command?->info("SchengenPortalSeeder: stamped {$updated} centre(s) with operator + booking URL.");
    }
}
