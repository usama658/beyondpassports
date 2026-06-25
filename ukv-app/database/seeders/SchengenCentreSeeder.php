<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SupplyNodeType;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;

class SchengenCentreSeeder extends Seeder
{
    /**
     * Seed one bookable supply-node "centre" per Schengen country so the
     * per-destination appointment board can attach availability.
     *
     * Re-run safe: nodes are keyed on `schengen-centre-{slug}` via updateOrCreate.
     * Creates NO centre_availability rows – the board stays honest-empty until
     * ops set real data.
     */
    public function run(): void
    {
        // Real UK visa-application-centre provider per destination slug, where confident.
        // Anything not listed falls back to "Provider: verify".
        $providers = [
            // TLScontact-run UK centres
            'france' => 'TLScontact',
            'switzerland' => 'TLScontact',
            'belgium' => 'TLScontact',
            // BLS International
            'spain' => 'BLS International',
            // VFS Global-run UK centres
            'germany' => 'VFS Global',
            'netherlands' => 'VFS Global',
            'austria' => 'VFS Global',
            'italy' => 'VFS Global',
            'portugal' => 'VFS Global',
            'greece' => 'VFS Global',
            'norway' => 'VFS Global',
            'sweden' => 'VFS Global',
            'denmark' => 'VFS Global',
            'finland' => 'VFS Global',
        ];

        // Central-London safe default (WC2). Feeds the existing geo finder;
        // accuracy refined later by ops.
        $defaultPostcode = 'WC2N 5DU';
        $defaultLat = 51.5074;
        $defaultLng = -0.1278;

        $destinations = Destination::where('visa_type', 'Schengen')->get();

        $count = 0;

        foreach ($destinations as $destination) {
            $provider = $providers[$destination->slug] ?? 'Provider: verify';

            $node = SupplyNode::updateOrCreate(
                ['node_key' => 'schengen-centre-'.$destination->slug],
                [
                    'type' => SupplyNodeType::Centre,
                    'we_book_here' => true,
                    'is_global' => false,
                    // EN DASH (–) between country name and London, not a hyphen, not an em dash.
                    'name' => $destination->name.' visa application centre – London',
                    'notes' => $provider.' – verify address/provider before relying',
                    'address' => null,
                    'postcode' => $defaultPostcode,
                    'lat' => $defaultLat,
                    'lng' => $defaultLng,
                ]
            );

            $node->destinations()->syncWithoutDetaching([$destination->id]);

            $count++;
        }

        echo "Seeded {$count} Schengen application-centre supply nodes.".PHP_EOL;
    }
}
