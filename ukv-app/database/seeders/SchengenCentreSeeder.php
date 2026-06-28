<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SupplyNodeType;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;

/**
 * Seed the bookable UK application centres per Schengen country, one row PER CITY where that
 * country's operator runs a centre, so the nearest-centre booking logic
 * (SlotService::holdForOrder) and the public finder have real geographic choices.
 *
 * Keys: the London centre keeps the canonical `schengen-centre-{slug}` key (so existing links /
 * the portal-operator seeder still match); other cities are `schengen-centre-{slug}-{city}`.
 * Re-run safe (updateOrCreate). Creates NO availability rows — the board stays honest-empty until
 * ops set real data. Coordinates are city-centre approximations; ops refine the exact centre
 * address. Operator + booking URL are stamped separately by SchengenPortalSeeder.
 */
class SchengenCentreSeeder extends Seeder
{
    /** city key => [display, lat, lng, postcode] (city-centre approximations) */
    private const CITIES = [
        'london'     => ['London', 51.5074, -0.1278, 'WC2N 5DU'],
        'manchester' => ['Manchester', 53.4808, -2.2426, 'M3 3HF'],
        'edinburgh'  => ['Edinburgh', 55.9533, -3.1883, 'EH3 9DR'],
        'birmingham' => ['Birmingham', 52.4862, -1.8904, 'B1 1AA'],
        'cardiff'    => ['Cardiff', 51.4816, -3.1791, 'CF10 1EP'],
    ];

    /** Country slug => city keys with a centre. Anything not listed uses DEFAULT_CITIES. */
    private const PER_COUNTRY = [
        'belgium'     => ['london', 'edinburgh'],
        'spain'       => ['london', 'manchester'],
        'netherlands' => ['london', 'manchester', 'edinburgh', 'birmingham'],
        'lithuania'   => ['london', 'manchester', 'edinburgh', 'cardiff'],
        // Embassy-direct: a single consular office (London) — no operator centre network.
        'luxembourg'  => ['london'],
        'poland'      => ['london'],
        'romania'     => ['london'],
        'slovakia'    => ['london'],
    ];

    /** Default UK centre cities for the major operators (VFS / TLScontact / GVCW). */
    private const DEFAULT_CITIES = ['london', 'manchester', 'edinburgh'];

    public function run(): void
    {
        $destinations = Destination::where('visa_type', 'Schengen')->get();
        $nodes = 0;

        foreach ($destinations as $destination) {
            $cityKeys = self::PER_COUNTRY[$destination->slug] ?? self::DEFAULT_CITIES;

            foreach ($cityKeys as $cityKey) {
                [$city, $lat, $lng, $postcode] = self::CITIES[$cityKey];

                // London keeps the canonical key; other cities are suffixed.
                $nodeKey = $cityKey === 'london'
                    ? 'schengen-centre-'.$destination->slug
                    : 'schengen-centre-'.$destination->slug.'-'.$cityKey;

                $node = SupplyNode::updateOrCreate(
                    ['node_key' => $nodeKey],
                    [
                        'type' => SupplyNodeType::Centre,
                        'we_book_here' => true,
                        'is_global' => false,
                        // EN DASH (–) between country name and city, not a hyphen.
                        'name' => $destination->name.' visa application centre – '.$city,
                        'address' => null,
                        'postcode' => $postcode,
                        'lat' => $lat,
                        'lng' => $lng,
                    ]
                );

                $node->destinations()->syncWithoutDetaching([$destination->id]);
                $nodes++;
            }
        }

        $this->command?->info("SchengenCentreSeeder: seeded {$nodes} application-centre nodes across ".$destinations->count().' countries.');
    }
}
