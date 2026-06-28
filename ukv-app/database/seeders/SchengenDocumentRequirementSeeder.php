<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\DocumentRequirement;
use Illuminate\Database\Seeder;

/**
 * Schengen-specific document checklist (conditional overlay on top of the per-destination
 * baseline 'core' rows). Feeds the same Document Requirements Engine the public
 * /document-checklist wizard and the per-order RequirementService both read from.
 *
 * Branches use the wizard's real input values:
 *   employment_status: employed | self_employed | student
 *   funding_source:    self | sponsor | employer
 *   residency_status:  citizen | permanent | visa_holder
 * All rows are scoped to Schengen destinations (conditions.destinations) so non-Schengen
 * destinations are unaffected. AND across condition keys, OR within an array, {} = everyone.
 *
 * Compliance: the closing-balance figure is NOT hard-coded — it varies by country and trip
 * length, so the note says we confirm it per destination (avoids stating a wrong fixed amount,
 * DMCCA). Insurance/passport/photo/funds/accommodation/visa-form are already in the baseline
 * required_docs, so they are not duplicated here.
 *
 * Idempotent: keyed on (document_key + conditions) via updateOrCreate.
 */
class SchengenDocumentRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $schengen = Destination::where('visa_type', 'Schengen')->pluck('slug')->all();

        if ($schengen === []) {
            return; // nothing to scope to
        }

        $rules = [
            // --- All Schengen applicants ------------------------------------------------------
            [
                'document_key' => 'cover-letter',
                'label'        => 'Cover letter',
                'note'         => 'A short letter explaining your trip: purpose, dates, who you are, and that you will return to the UK. We help you draft this.',
                'category'     => 'core',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => true,
                'sort_order'   => 6,
            ],
            [
                'document_key' => 'travel-itinerary',
                'label'        => 'Day-by-day travel itinerary',
                'note'         => 'A plan of your stay with no unexplained gaps, matching your bookings and visa dates.',
                'category'     => 'logistics',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => false,
                'sort_order'   => 24,
            ],
            [
                'document_key' => 'uk-ties-evidence',
                'label'        => 'Evidence of strong UK ties',
                'note'         => 'Proof you will return to the UK, e.g. employment, tenancy/mortgage, studies or family. Reduces refusal risk.',
                'category'     => 'core',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => false,
                'sort_order'   => 7,
            ],
            [
                'document_key' => 'appointment-confirmation',
                'label'        => 'Visa-centre appointment confirmation',
                'note'         => 'Confirmation of your biometrics appointment at the application centre. We book this for you.',
                'category'     => 'logistics',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => true,
                'sort_order'   => 25,
            ],
            [
                'document_key' => 'bank-statements-3m-recent',
                'label'        => 'Bank statements — last 3 months',
                'note'         => 'Your most recent 3 months of statements, dated within the last month (consulates reject older printouts). Stamped or via online banking PDF.',
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => true,
                'sort_order'   => 31,
            ],
            [
                'document_key' => 'closing-balance',
                'label'        => 'Sufficient closing balance',
                'note'         => 'Your statements should show enough funds for the trip. The minimum varies by country and length of stay — we confirm the exact figure for your destination.',
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen],
                'mandatory'    => true,
                'sort_order'   => 32,
            ],
            [
                'document_key' => 'uk-status-share-code',
                'label'        => 'UK immigration status share code',
                'note'         => 'For non-British nationals: a Home Office share code (plus your visa/BRP) proving your UK residence and that it is valid beyond your travel dates.',
                'category'     => 'identity',
                'conditions'   => ['destinations' => $schengen, 'residency_status' => ['permanent', 'visa_holder']],
                'mandatory'    => true,
                'sort_order'   => 12,
            ],

            // --- Employed ---------------------------------------------------------------------
            [
                'document_key' => 'employer-leave-letter',
                'label'        => 'Employer letter confirming leave',
                'note'         => 'On company letterhead: your role, salary, length of service and approved leave dates for the trip.',
                'category'     => 'logistics',
                'conditions'   => ['destinations' => $schengen, 'employment_status' => ['employed']],
                'mandatory'    => true,
                'sort_order'   => 22,
            ],
            [
                'document_key' => 'payslips-3m',
                'label'        => 'Last 3 months of payslips',
                'note'         => 'Recent payslips matching the salary credits on your bank statements.',
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen, 'employment_status' => ['employed', 'self_employed']],
                'mandatory'    => true,
                'sort_order'   => 33,
            ],

            // --- Student ----------------------------------------------------------------------
            [
                'document_key' => 'university-enrolment-letter',
                'label'        => 'University / college enrolment letter',
                'note'         => 'A letter from your institution confirming enrolment and term dates, showing you will return to your studies.',
                'category'     => 'logistics',
                'conditions'   => ['destinations' => $schengen, 'employment_status' => ['student']],
                'mandatory'    => true,
                'sort_order'   => 23,
            ],

            // --- Sponsored / third-party funding (individual OR company) ----------------------
            [
                'document_key' => 'sponsor-passport-id',
                'label'        => "Sponsor's passport / ID",
                'note'         => 'A copy of the photo page of whoever is funding your trip (parent, spouse or company signatory).',
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen, 'funding_source' => ['sponsor', 'employer']],
                'mandatory'    => true,
                'sort_order'   => 52,
            ],
            [
                'document_key' => 'sponsor-relationship-proof',
                'label'        => 'Proof of relationship to sponsor',
                'note'         => 'E.g. birth or marriage certificate (parent/spouse), or for a company sponsor, a letter on letterhead confirming they cover your costs.',
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen, 'funding_source' => ['sponsor', 'employer']],
                'mandatory'    => true,
                'sort_order'   => 53,
            ],
            [
                'document_key' => 'sponsor-funds-evidence',
                'label'        => "Sponsor's funds — statements / payslips",
                'note'         => "Your sponsor's last 3 months of bank statements (and payslips if employed) showing they can support your trip.",
                'category'     => 'funding',
                'conditions'   => ['destinations' => $schengen, 'funding_source' => ['sponsor', 'employer']],
                'mandatory'    => true,
                'sort_order'   => 54,
            ],
        ];

        foreach ($rules as $rule) {
            DocumentRequirement::updateOrCreate(
                ['document_key' => $rule['document_key'], 'conditions' => $rule['conditions']],
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

        $this->command?->info('SchengenDocumentRequirementSeeder: seeded '.count($rules).' Schengen checklist rules across '.count($schengen).' countries.');
    }
}
