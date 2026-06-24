<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Schengen short-stay (Type C) visa destinations, grouped by region. visa_type = 'Schengen'
 * is the marker the public site gates on (home grid, /visa/schengen hub, money pages, sitemap).
 *
 * COMPLIANCE / ACCURACY:
 *   - British passport holders DO NOT need a Schengen visa: visa-free 90/180, with ETIAS from
 *     late 2026. So required_for_uk = false. These pages serve VISA-REQUIRED nationals, including
 *     UK residents applying on a non-UK passport. Page copy states this plainly; we never imply a
 *     British passport holder needs a paid visa.
 *   - Schengen short-stay visa fee is uniform: EUR 90 adult (EUR 45 child 6-11). govt_fee_gbp = 77
 *     is the EUR 90 GBP approximation — VERIFY the fee + FX immediately before taking live payment.
 *   - Schengen short-stay passport rule: valid for at least 3 months beyond intended departure,
 *     issued within the last 10 years -> passport_validity_months = 3.
 *   - Standard consular processing ~15 calendar days (can be longer in peak season).
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
            'Completed Schengen short-stay (Type C) visa application form',
            'Passport valid for at least 3 months beyond your departure, issued within the last 10 years, with 2 blank pages',
            'Two recent passport-style photos to Schengen specification',
            'Travel medical insurance covering the whole Schengen Area, minimum EUR 30,000',
            'Proof of accommodation and round-trip travel booking for the whole stay',
            'Evidence of sufficient funds for your stay (recent bank statements)',
            'Proof of UK residence / immigration status (for non-British passport holders applying from the UK)',
        ];

        foreach ($countries as [$name, $slug, $region]) {
            Destination::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'visa_type' => 'Schengen',
                    'region' => $region,
                    // 'required_for_uk' = does a BRITISH passport holder need a visa: no (visa-free,
                    // ETIAS from late 2026). These pages serve visa-required nationals (incl. UK
                    // residents on a non-UK passport). Page copy makes that audience clear.
                    'required_for_uk' => false,
                    'max_stay_days' => 90,            // 90 days in any 180-day period
                    'govt_fee_gbp' => 77.00,          // Schengen short-stay visa fee EUR 90 ≈ £77 (uniform; VERIFY + FX before live)
                    'tier_standard_gbp' => 49.00,     // placeholder service fee (hidden while prices off)
                    'tier_express_gbp' => 69.00,
                    'tier_premium_gbp' => 99.00,
                    'passport_validity_months' => 3,  // Schengen short-stay rule
                    'processing_days' => 15,          // standard Schengen consular processing (15 calendar days typical)
                    'required_docs' => $docs,
                ]
            );
        }
    }
}
