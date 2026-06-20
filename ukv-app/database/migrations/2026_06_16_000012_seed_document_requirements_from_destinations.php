<?php

use App\Models\Destination;
use App\Models\DocumentRequirement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Data migration — Document Requirements Engine, day-one parity.
 *
 * Converts each destination's existing `destinations.required_docs` string array into baseline
 * DocumentRequirement rows so the engine has real data the moment it ships. Each row is a
 * mandatory 'core' rule scoped to its destination via conditions {"destinations":["<slug>"]}.
 *
 * Mapping per required_docs entry:
 *   document_key = Str::slug(label)                 (stable slug; e.g. "Passport-style digital photo" -> "passport-style-digital-photo")
 *   label        = the original string (verbatim, customer-facing)
 *   note         = null                             (the strings are already self-describing)
 *   category     = 'core'
 *   conditions   = {"destinations":["<slug>"]}
 *   mandatory    = true
 *   active       = true
 *   sort_order   = position within that destination's list (0-based)
 *
 * Idempotent: skips when a row already exists with the same document_key AND a conditions
 * payload scoping it to that destination. Re-running never duplicates.
 *
 * Reversible: down() removes only the destination-scoped 'core' rows this migration could have
 * created (conditions exactly {"destinations":[<slug>]}). Conditional example rules added by
 * DocumentRequirementSeeder use richer conditions and are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Destination::query()->get() as $destination) {
            $slug = $destination->slug;

            $docs = collect($destination->required_docs ?? [])
                ->map(fn ($d) => is_string($d) ? trim($d) : null)
                ->filter()
                ->values();

            foreach ($docs as $i => $label) {
                $key = Str::slug($label);
                if ($key === '') {
                    continue; // skip labels that slugify to nothing
                }
                // document_key is varchar(80); long sentence-style labels (e.g. Schengen docs)
                // slugify past that. Cap deterministically with a short hash to stay unique.
                if (strlen($key) > 80) {
                    $key = substr($key, 0, 71).'-'.substr(md5($label), 0, 8);
                }

                // Idempotency: a row for this document_key already scoped to this destination?
                $exists = DocumentRequirement::query()
                    ->where('document_key', $key)
                    ->whereJsonContains('conditions->destinations', $slug)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DocumentRequirement::create([
                    'document_key' => $key,
                    'label'        => $label,
                    'note'         => null,
                    'category'     => 'core',
                    'conditions'   => ['destinations' => [$slug]],
                    'mandatory'    => true,
                    'active'       => true,
                    'sort_order'   => $i,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove only the single-destination-scoped 'core' rows this migration creates.
        foreach (Destination::query()->pluck('slug') as $slug) {
            DocumentRequirement::query()
                ->where('category', 'core')
                ->where('conditions', json_encode(['destinations' => [$slug]]))
                ->delete();
        }
    }
};
