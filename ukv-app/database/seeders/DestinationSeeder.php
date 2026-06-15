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
 * ============================================================================
 * RESEARCHED-BUT-UNCONFIRMED GOVT DATA — DO NOT LAUNCH WITHOUT VERIFICATION.
 * ============================================================================
 * The visa_type / govt_fee_gbp / max_stay_days / passport_validity_months /
 * required_docs values below were researched from gov.uk + issuing-authority /
 * secondary sources (access date 2026-06-16) and applied from:
 *   source: docs/superpowers/port/06-destination-data.md
 * FX used for GBP conversions: USD 1 ≈ £0.745, AUD 1 ≈ £0.525 (rounded to whole £).
 *
 * These figures are a BASELINE ONLY. Visa rules and fees change frequently and
 * several values are seasonal or come from secondary sources. EVERY value MUST
 * be re-verified by the operator against gov.uk AND each issuing authority
 * IMMEDIATELY BEFORE GO-LIVE. Known unverified / volatile items:
 *   - India fee: SEASONAL (US$25 Jul–Mar / US$10 Apr–Jun) + 2.5% bank fee, and
 *     the official portal (indianvisaonline.gov.in) could NOT be fetched —
 *     fee is UNVERIFIED. A single static govt_fee_gbp cannot capture both bands.
 *   - USA ESTA US$40.27: not quoted on gov.uk; secondary sources only.
 *   - Australia AUD$20 service charge: gov.uk confirms a fee exists but not the amount.
 *   - Egypt / Vietnam eVisa fees: from secondary guides (Egypt VOA $30 is gov.uk-confirmed).
 *   - passport_validity_months is an integer-months field; Turkey's real rule is
 *     150 days + 1 blank page (encoded as 5 with a comment) — not a clean month count.
 *
 * Service-fee tiers (tier_*_gbp) are a SEPARATE commercial decision, are NOT part
 * of this research, and mirror the frontend "from" prices
 * (frontend/destinations.html + destination.html: Turkey £39/£59/£89, etc.).
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
                // source: 06-destination-data.md — Turkey is now VISA-FREE for UK citizens (e-visa no longer required). VERIFY before go-live.
                'visa_type' => 'Visa-free',
                'required_for_uk' => false,
                'max_stay_days' => 90, // 90 days in any 180-day period
                'govt_fee_gbp' => 0.00, // visa-free — no govt fee
                'tier_standard_gbp' => 39.00,
                'tier_express_gbp' => 59.00,
                'tier_premium_gbp' => 89.00,
                'passport_validity_months' => 5, // actually ≥150 days after arrival + 1 blank page (NOT the usual 6-month rule)
                'required_docs' => [
                    'Passport valid 150+ days beyond arrival, with 1 blank page',
                    'Onward / return travel details',
                    'Proof of accommodation',
                ],
            ],
            [
                'name' => 'Egypt',
                'slug' => 'egypt',
                // source: 06-destination-data.md — visa required (eVisa or VOA). gov.uk confirms VOA US$30 cash. VERIFY before go-live.
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 30,
                'govt_fee_gbp' => 22.00, // VOA US$30 ≈ £22 (corrected up from $25 placeholder)
                'tier_standard_gbp' => 49.00,
                'tier_express_gbp' => 69.00,
                'tier_premium_gbp' => 99.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'India',
                'slug' => 'india',
                // source: 06-destination-data.md — e-Tourist Visa. FEE IS SEASONAL & UNVERIFIED: US$25 Jul–Mar / US$10 Apr–Jun + 2.5% bank fee; official portal could not be fetched. Single static value is lossy — VERIFY live before go-live.
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 30,
                'govt_fee_gbp' => 19.00, // Jul–Mar standard US$25 ≈ £19 baseline (Apr–Jun ≈ £8) — SEASONAL/UNVERIFIED
                'tier_standard_gbp' => 55.00,
                'tier_express_gbp' => 75.00,
                'tier_premium_gbp' => 105.00,
                'passport_validity_months' => 6,
                'required_docs' => $eVisaDocs,
            ],
            [
                'name' => 'USA (ESTA)',
                'slug' => 'usa-esta',
                // source: 06-destination-data.md — ESTA (VWP). Fee US$40.27 NOT quoted on gov.uk (secondary sources only) — VERIFY on esta.cbp.dhs.gov before go-live.
                'visa_type' => 'ETA',
                'required_for_uk' => true,
                'max_stay_days' => 90,
                'govt_fee_gbp' => 30.00, // ESTA US$40.27 ≈ £30 (was stale $21/£17)
                'tier_standard_gbp' => 35.00,
                'tier_express_gbp' => 55.00,
                'tier_premium_gbp' => 85.00,
                'passport_validity_months' => 6,
                'required_docs' => $etaDocs,
            ],
            [
                'name' => 'Australia',
                'slug' => 'australia-eta',
                // source: 06-destination-data.md — ETA (subclass 601). No visa charge; AUD$20 app service fee is from secondary guides (gov.uk confirms a fee exists, not the amount) — VERIFY before go-live.
                'visa_type' => 'eTA',
                'required_for_uk' => true,
                'max_stay_days' => 90, // up to 3 months per entry; 12-month validity
                'govt_fee_gbp' => 11.00, // AUD$20 service charge ≈ £10.50 → £11 (UNVERIFIED amount)
                'tier_standard_gbp' => 39.00,
                'tier_express_gbp' => 59.00,
                'tier_premium_gbp' => 89.00,
                'passport_validity_months' => 6,
                'required_docs' => $etaDocs,
            ],
            [
                'name' => 'Thailand',
                'slug' => 'thailand',
                // source: 06-destination-data.md — visa-free (visa exemption). TDAC (Thailand Digital Arrival Card) is mandatory but FREE. VERIFY before go-live.
                'visa_type' => 'Visa-free',
                'required_for_uk' => false,
                'max_stay_days' => 60,
                'govt_fee_gbp' => 0.00, // visa-free; TDAC is free
                'tier_standard_gbp' => 0.00, // guide only — no service fee
                'tier_express_gbp' => null,
                'tier_premium_gbp' => null,
                'passport_validity_months' => 6, // 6 months after arrival + 1 blank page
                'required_docs' => [
                    'Passport valid 6+ months, 1 blank page',
                    'Thailand Digital Arrival Card (TDAC) — free, within 3 days of arrival',
                    'Onward / return travel details',
                    'Proof of accommodation',
                ],
            ],
            [
                'name' => 'UAE',
                'slug' => 'uae',
                // source: 06-destination-data.md — FREE visa-on-arrival (not a pre-applied eVisa); stay upgraded to 90 days Aug 2024. VERIFY before go-live.
                'visa_type' => 'Visa on arrival',
                'required_for_uk' => false,
                'max_stay_days' => 90, // 90 days in any 180-day period (was stale 30)
                'govt_fee_gbp' => 0.00, // visa issued free on arrival
                'tier_standard_gbp' => 45.00,
                'tier_express_gbp' => 65.00,
                'tier_premium_gbp' => 95.00,
                'passport_validity_months' => 6, // 6 months after arrival
                'required_docs' => [
                    'Passport valid 6+ months',
                    'Onward / return travel details',
                    'Proof of accommodation',
                ],
            ],
            [
                'name' => 'Vietnam',
                'slug' => 'vietnam',
                // source: 06-destination-data.md — visa-free up to 45 days OR eVisa (90-day, multiple entry) for longer. eVisa fee US$25 single is from secondary guides — VERIFY on evisa.gov.vn before go-live.
                'visa_type' => 'eVisa',
                'required_for_uk' => true,
                'max_stay_days' => 90, // eVisa path (45 days needs no visa at all)
                'govt_fee_gbp' => 19.00, // eVisa single US$25 ≈ £19 (UNVERIFIED amount)
                'tier_standard_gbp' => 45.00,
                'tier_express_gbp' => 65.00,
                'tier_premium_gbp' => 95.00,
                'passport_validity_months' => 6, // 6 months after arrival + 2 blank pages
                'required_docs' => $eVisaDocs, // note: passport needs 2 blank pages
            ],
        ];

        foreach ($destinations as $data) {
            $slug = $data['slug'];
            unset($data['slug']);

            Destination::updateOrCreate(['slug' => $slug], $data);
        }
    }
}
