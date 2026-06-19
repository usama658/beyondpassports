<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Set destinations.image_path to the self-hosted stock photo for each destination.
 *
 * The migration 2026_06_18_000030 only ADDS the nullable column; the path values were
 * originally set by hand on the demo DB and never captured as a seeder — so production
 * had NULL image_path and the home/money cards fell back to the skyline SVG. This seeder
 * makes it reproducible: path = /assets/img/destinations/{slug}.jpg, set only when the
 * file actually exists in public/, idempotent (safe to re-run).
 */
final class DestinationImageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Destination::all() as $destination) {
            $relative = 'assets/img/destinations/'.$destination->slug.'.jpg';

            if (! is_file(public_path($relative))) {
                continue; // no photo shipped for this slug — leave null (skyline fallback)
            }

            $path = '/'.$relative;
            if ($destination->image_path !== $path) {
                $destination->forceFill(['image_path' => $path])->save();
            }
        }
    }
}
