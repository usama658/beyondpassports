<?php

namespace Database\Seeders;

use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

/**
 * Seeds a handful of CONDITIONAL example rules for the Document Requirements Engine.
 *
 * These complement the destination-scoped baseline 'core' rows created by the
 * 2026_06_16_000012_seed_document_requirements_from_destinations data migration. Together they
 * give the engine realistic, admin-editable data covering the common case-shaping situations.
 *
 * conditions semantics (per the design spec): all keys optional, AND across keys, OR within an
 * array, {} = applies to everyone. passport_validity_short is a COMPUTED flag the service derives
 * from passport_expiry / travel_date / destination passport_validity_months.
 *
 * Idempotent: keyed on (document_key + conditions). updateOrCreate refreshes the label/note/etc.
 * in place on re-run rather than duplicating. Safe to call repeatedly.
 */
class DocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // --- All applicants (core, {} = always) -------------------------------------------
            [
                'document_key' => 'passport-style-photo-spec',
                'label'        => 'A compliant passport-style photo',
                'note'         => 'Recent, clear, plain light background, no glasses or head covering (except for religious reasons). We confirm the exact pixel/size spec for your destination.',
                'category'     => 'core',
                'conditions'   => [],
                'mandatory'    => true,
                'sort_order'   => 5,
            ],

            // --- Business trip ----------------------------------------------------------------
            [
                'document_key' => 'invitation-letter-business',
                'label'        => 'Business invitation letter',
                'note'         => 'A letter from the company or contact you are visiting, on letterhead, stating the purpose and dates of your trip.',
                'category'     => 'logistics',
                'conditions'   => ['trip_purpose' => ['business']],
                'mandatory'    => true,
                'sort_order'   => 20,
            ],
            [
                'document_key' => 'employer-letter',
                'label'        => 'Employer letter',
                'note'         => 'A letter from your employer confirming your role, that you remain employed, and your approved leave for the trip.',
                'category'     => 'logistics',
                'conditions'   => ['trip_purpose' => ['business']],
                'mandatory'    => false,
                'sort_order'   => 21,
            ],

            // --- Minor (under 18) -------------------------------------------------------------
            [
                'document_key' => 'birth-certificate',
                'label'        => "Traveller's birth certificate",
                'note'         => 'Full birth certificate showing the parents, to establish the relationship for a traveller under 18.',
                'category'     => 'identity',
                'conditions'   => ['is_minor' => true],
                'mandatory'    => true,
                'sort_order'   => 10,
            ],
            [
                'document_key' => 'parental-consent-letter',
                'label'        => 'Parental / guardian consent letter',
                'note'         => 'A signed letter from the parent(s) or legal guardian consenting to the trip, with a copy of their ID.',
                'category'     => 'identity',
                'conditions'   => ['is_minor' => true],
                'mandatory'    => true,
                'sort_order'   => 11,
            ],

            // --- Self-employed ----------------------------------------------------------------
            [
                'document_key' => 'business-registration',
                'label'        => 'Business registration document',
                'note'         => 'Proof your business is registered (e.g. Companies House record or equivalent) for self-employed travellers.',
                'category'     => 'funding',
                'conditions'   => ['employment_status' => ['self_employed']],
                'mandatory'    => true,
                'sort_order'   => 30,
            ],

            // --- Staying with a host ----------------------------------------------------------
            [
                'document_key' => 'host-invitation-letter',
                'label'        => 'Host invitation letter',
                'note'         => "A letter from the person you'll stay with, confirming your accommodation, their address and the dates.",
                'category'     => 'logistics',
                'conditions'   => ['accommodation_type' => ['host']],
                'mandatory'    => true,
                'sort_order'   => 40,
            ],
            [
                'document_key' => 'host-id',
                'label'        => "A copy of your host's ID",
                'note'         => "A copy of the host's passport or national ID, and proof they live at the address (e.g. a utility bill).",
                'category'     => 'logistics',
                'conditions'   => ['accommodation_type' => ['host']],
                'mandatory'    => true,
                'sort_order'   => 41,
            ],

            // --- Sponsored funding ------------------------------------------------------------
            [
                'document_key' => 'sponsor-letter',
                'label'        => 'Sponsor letter',
                'note'         => 'A letter from whoever is funding your trip, confirming they will cover your costs, with their relationship to you.',
                'category'     => 'funding',
                'conditions'   => ['funding_source' => ['sponsored']],
                'mandatory'    => true,
                'sort_order'   => 50,
            ],
            [
                'document_key' => 'sponsor-bank-statements',
                'label'        => "Sponsor's recent bank statements",
                'note'         => "Recent statements (usually the last 3 months) showing your sponsor has the funds to support your trip.",
                'category'     => 'funding',
                'conditions'   => ['funding_source' => ['sponsored']],
                'mandatory'    => true,
                'sort_order'   => 51,
            ],

            // --- Prior visa refusal -----------------------------------------------------------
            [
                'document_key' => 'refusal-explanation-letter',
                'label'        => 'Explanation letter for a previous refusal',
                'note'         => 'A short letter explaining the circumstances of any previous visa refusal and what has changed since. We help you prepare this.',
                'category'     => 'core',
                'conditions'   => ['prior_refusal' => true],
                'mandatory'    => true,
                'sort_order'   => 60,
            ],

            // --- Passport validity too short (computed) ---------------------------------------
            [
                'document_key' => 'renew-passport-first',
                'label'        => 'Renew your passport before applying',
                'note'         => "Your passport may not meet the destination's validity rule for your travel dates. We strongly recommend renewing it before we submit, to avoid a refusal.",
                'category'     => 'identity',
                'conditions'   => ['passport_validity_short' => true],
                'mandatory'    => true,
                'sort_order'   => 1,
            ],
        ];

        foreach ($rules as $rule) {
            // Idempotency key: document_key + the exact conditions payload. Matching on conditions
            // too means the same key can legitimately appear under different condition sets.
            DocumentRequirement::updateOrCreate(
                [
                    'document_key' => $rule['document_key'],
                    'conditions'   => $rule['conditions'],
                ],
                [
                    'label'      => $rule['label'],
                    'note'       => $rule['note'],
                    'category'   => $rule['category'],
                    'mandatory'  => $rule['mandatory'],
                    'active'     => true,
                    'sort_order' => $rule['sort_order'],
                ],
            );
        }
    }
}
