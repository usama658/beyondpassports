<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production-safe reference data. Runs on every deploy (via scripts/update-a2.sh).
 *
 * ONLY idempotent, real-content seeders belong here — every one below uses
 * updateOrCreate (or a guarded save), so re-running never duplicates or wipes data.
 * Deliberately EXCLUDES DatabaseSeeder's demo artefacts (Test User, DemoOrderSeeder,
 * FinderDemoSeeder) — those are local/dev only and must never touch production.
 *
 * Order matters: destinations first (rows), then anything that decorates them
 * (Schengen ETIAS rows, image paths, doc requirements).
 *
 *   php artisan db:seed --class=ProductionSeeder --force
 */
final class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DestinationSeeder::class,        // 8 core money-page destinations
            SchengenSeeder::class,           // 29 Schengen / ETIAS countries + region
            DestinationImageSeeder::class,   // image_path for any slug whose .jpg exists
            DocumentRequirementSeeder::class,// per-destination required docs
            SupplyNodeSeeder::class,         // centres / couriers / suppliers
            SchengenCentreSeeder::class,     // one bookable centre per Schengen country (board)
            SampleAvailabilitySeeder::class, // TEMP illustrative board data (decays in 7d; remove once ops own it)
            TurkeyGoldGuidesSeeder::class,   // published guides
        ]);
    }
}
