<?php

use App\Models\Destination;
use App\Models\DocumentRequirement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Backfill: convert any destination required_docs that were NOT turned into
 * DocumentRequirement rows by 2026_06_16_000012.
 *
 * Why this exists: the original conversion ran before the Schengen/ETIAS
 * destinations were seeded, AND its raw Str::slug() exceeded document_key's
 * varchar(80) for sentence-style Schengen labels, so those 29 destinations
 * never got core rules (their checklists showed only the global photo rule).
 *
 * Idempotent: skips any (document_key scoped to that destination) that already
 * exists. Key is length-capped to fit varchar(80), matching the fixed 000012.
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
                    continue;
                }
                if (strlen($key) > 80) {
                    $key = substr($key, 0, 71).'-'.substr(md5($label), 0, 8);
                }

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
        // Non-reversible data backfill; leave rows in place.
    }
};
