<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Schengen Area destinations, grouped by region, for the ETIAS silo.
 *
 * COMPLIANCE / ACCURACY (verified 2026-06-20 from EU + secondary sources):
 *   - ETIAS is NOT yet live. It launches Q4 2026 and is not mandatory until ~April 2027
 *     (6-month transitional period). RIGHT NOW UK citizens travel to the Schengen Area
 *     VISA-FREE — no ETIAS, no government fee. So required_for_uk = false and
 *     govt_fee_gbp = 0 reflect TODAY'S reality; the page copy/banner explains ETIAS is coming.
 *   - When live: ETIAS will cost EUR 20 (free under-18 / over-70), valid 3 years or until the
 *     passport expires, multiple entries, short stays of 90 days in any 180.
 *   - Schengen short-stay passport rule: valid for at least 3 months beyond intended departure,
 *     issued within the last 10 years -> passport_validity_months = 3.
 *   - Service-fee tiers are a commercial placeholder, hidden while UKV_SHOW_PRICES=false.
 *
 * Re-run safe (updateOrCreate on slug). Run after DestinationSeeder + the region migration.
 */
final class SchengenSeeder extends Seeder
{
    public function run(): void
    {
        $west = 'Western Europe';
        $south = 'Southern Europe';
        $north = 'Northern Europe';
        $east = 'Central & Eastern Europe';

        // [name, slug, region]
        $countries = [
            // Western Europe
            ['France', 'france', $west],
            ['Germany', 'germany', $west],
            ['Netherlands', 'netherlands', $west],
            ['Austria', 'austria', $west],
            ['Belgium', 'belgium', $west],
            ['Luxembourg', 'luxembourg', $west],
            ['Switzerland', 'switzerland', $west],
            ['Liechtenstein', 'liechtenstein', $west],
            // Southern Europe
            ['Spain', 'spain', $south],
            ['Italy', 'italy', $south],
            ['Portugal', 'portugal', $south],
            ['Greece', 'greece', $south],
            ['Croatia', 'croatia', $south],
            ['Malta', 'malta', $south],
            ['Slovenia', 'slovenia', $south],
            // Northern Europe
            ['Denmark', 'denmark', $north],
            ['Sweden', 'sweden', $north],
            ['Iceland', 'iceland', $north],
            ['Norway', 'norway', $north],
            ['Finland', 'finland', $north],
            // Central & Eastern Europe
            ['Poland', 'poland', $east],
            ['Czechia', 'czechia', $east],
            ['Hungary', 'hungary', $east],
            ['Estonia', 'estonia', $east],
            ['Latvia', 'latvia', $east],
            ['Lithuania', 'lithuania', $east],
            ['Slovakia', 'slovakia', $east],
            ['Bulgaria', 'bulgaria', $east],
            ['Romania', 'romania', $east],
        ];

        $docs = [
            'Passport valid for at least 3 months beyond your departure from the Schengen Area, issued within the last 10 years',
            'Proof of accommodation and onward / return travel',
            'Evidence of sufficient funds for your stay',
            'From late 2026: an approved ETIAS travel authorisation (EUR 20; valid 3 years; 90 days in any 180)',
        ];

        foreach ($countries as [$name, $slug, $region]) {
            Destination::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'visa_type' => 'ETIAS',
                    'region' => $region,
                    // Visa-free for UK citizens TODAY; ETIAS only required from late 2026.
                    'required_for_uk' => false,
                    'max_stay_days' => 90,            // 90 days in any 180-day period
                    'govt_fee_gbp' => 0.00,           // visa-free now; ETIAS EUR 20 applies from late 2026
                    'tier_standard_gbp' => 39.00,     // placeholder service fee (hidden while prices off)
                    'tier_express_gbp' => 59.00,
                    'tier_premium_gbp' => 89.00,
                    'passport_validity_months' => 3,  // Schengen short-stay rule
                    'required_docs' => $docs,
                ]
            );
        }
    }
}
