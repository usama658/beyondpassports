<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentreSlot;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * DEMO centres + appointment slots so the nearest-centre finder shows something before real
 * supply-chain data is entered (#95). Clearly marked DEMO in notes; overwrite/replace with real
 * verified centres + slots before launch. Idempotent (updateOrCreate on node_key).
 */
class FinderDemoSeeder extends Seeder
{
    public function run(): void
    {
        // [node_key, type, name, postcode, lat, lng, we_book_here, contact]
        $centres = [
            ['demo-vac-london', 'centre', 'DEMO — London Visa Application Centre', 'WC2N 5DU', 51.5074, -0.1278, true, 'https://example.com/london'],
            ['demo-vac-manchester', 'centre', 'DEMO — Manchester Visa Application Centre', 'M1 2WD', 53.4808, -2.2426, true, 'https://example.com/manchester'],
            ['demo-vac-birmingham', 'centre', 'DEMO — Birmingham Visa Application Centre', 'B2 4QA', 52.4862, -1.8904, false, 'https://example.com/birmingham'],
            ['demo-paypoint-edinburgh', 'paypoint', 'DEMO — Edinburgh PayPoint (IDP)', 'EH1 1BB', 55.9533, -3.1883, false, 'https://www.paypoint.com/en-gb/consumers/store-locator'],
        ];

        foreach ($centres as [$key, $type, $name, $postcode, $lat, $lng, $books, $contact]) {
            $node = SupplyNode::updateOrCreate(
                ['node_key' => $key],
                [
                    'type' => $type,
                    'name' => $name,
                    'postcode' => $postcode,
                    'address' => 'DEMO address — replace with the real centre address',
                    'lat' => $lat,
                    'lng' => $lng,
                    'we_book_here' => $books,
                    'contact' => $contact,
                    'notes' => 'DEMO seed data — replace with a real verified centre before launch.',
                    'is_global' => false,
                ],
            );

            // Seed a few upcoming available slots on the centres we "book at".
            if ($books) {
                foreach ([3, 5, 8, 12] as $days) {
                    $slotAt = Carbon::now()->addDays($days)->setTime(10, 0);
                    CentreSlot::updateOrCreate(
                        ['supply_node_id' => $node->getKey(), 'slot_at' => $slotAt],
                        ['status' => 'available', 'hold_expires_at' => null, 'order_id' => null],
                    );
                }
            }
        }

        $this->command?->info('FinderDemoSeeder: 4 demo centres (+ slots on 2) seeded.');
    }
}
