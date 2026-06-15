<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Seeds the destinations catalogue used by the public destination pages and the
 * admin pricing/eligibility screens.
 *
 * Idempotent: keyed on `slug` via updateOrCreate, so re-running refreshes the
 * row in place rather than duplicating.
 *
 * PLACEHOLDER FIGURES — service fees mirror the frontend "from" prices
 * (frontend/destinations.html + destination.html: Turkey £39/£59/£89, etc.).
 * govt_fee_gbp values are representative estimates only and MUST be verified
 * against gov.uk / the issuing authority before launch. max_stay_days and
 * required_docs are indicative.
 */
class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        // Common document baselines reused across destinations.
        $eVisaDocs = [
            'Passport bio page scan (clear, in colour)',
            'Passport-style digital photo',
            'Travel / accommodation details',
        ];

        $etaDocs = [
            'Passport bio page scan (clear, in colour)',
            'Passport-style digital photo',
            'Email address linked to the authorisation',
        ];

        $destinations = [
            [
                'name' => 'Turkey',
                'slug' => 'turkey',
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 90,
                'govt_fee_gbp' => 0.00, // Turkey eVisa is free for UK citizens (handling fee only) — verify
                'tier_standard_gbp' => 39.00,
                'tier_express_gbp' => 59.00,
                'tier_premium_gbp' => 89.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'Egypt',
                'slug' => 'egypt',
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 30,
                'govt_fee_gbp' => 20.00, // ~US$25 single-entry — verify
                'tier_standard_gbp' => 49.00,
                'tier_express_gbp' => 69.00,
                'tier_premium_gbp' => 99.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'India',
                'slug' => 'india',
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 30,
                'govt_fee_gbp' => 30.00, // e-Tourist 30-day band — verify
                'tier_standard_gbp' => 55.00,
                'tier_express_gbp' => 75.00,
                'tier_premium_gbp' => 105.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'USA (ESTA)',
                'slug' => 'usa-esta',
                'visa_type' => 'ETA',
                'required_for_uk' => true,
                'max_stay_days' => 90,
                'govt_fee_gbp' => 17.00, // ESTA US$21 — verify
                'tier_standard_gbp' => 35.00,
                'tier_express_gbp' => 55.00,
                'tier_premium_gbp' => 85.00,
                'passport_validity_months' => 6,
                'required_docs' => $etaDocs,
            ],
            [
                'name' => 'Australia',
                'slug' => 'australia-eta',
                'visa_type' => 'eTA',
                'required_for_uk' => true,
                'max_stay_days' => 90,
                'govt_fee_gbp' => 11.00, // ETA service charge AU$20 — verify
                'tier_standard_gbp' => 39.00,
                'tier_express_gbp' => 59.00,
                'tier_premium_gbp' => 89.00,
                'passport_validity_months' => 6,
                'required_docs' => $etaDocs,
            ],
            [
                'name' => 'Thailand',
                'slug' => 'thailand',
                'visa_type' => 'Visa-free',
                'required_for_uk' => false,
                'max_stay_days' => 60,
                'govt_fee_gbp' => 0.00, // visa-free entry for UK citizens — verify
                'tier_standard_gbp' => 0.00, // guide only — no service fee
                'tier_express_gbp' => null,
                'tier_premium_gbp' => null,
                'passport_validity_months' => 6,
                'required_docs' => [
                    'Passport valid 6+ months',
                    'Onward / return travel details',
                    'Proof of accommodation',
                ],
            ],
            [
                'name' => 'UAE',
                'slug' => 'uae',
                'visa_type' => 'eVisa',
                'required_for_uk' => false,
                'max_stay_days' => 30,
                'govt_fee_gbp' => 0.00, // 30-day visa-on-arrival waiver for UK citizens — verify
                'tier_standard_gbp' => 45.00,
                'tier_express_gbp' => 65.00,
                'tier_premium_gbp' => 95.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'Vietnam',
                'slug' => 'vietnam',
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 90,
                'govt_fee_gbp' => 20.00, // e-Visa US$25 single-entry — verify
                'tier_standard_gbp' => 45.00,
                'tier_express_gbp' => 65.00,
                'tier_premium_gbp' => 95.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
        ];

        foreach ($destinations as $data) {
            $slug = $data['slug'];
            unset($data['slug']);

            Destination::updateOrCreate(['slug' => $slug], $data);
        }
    }
}
