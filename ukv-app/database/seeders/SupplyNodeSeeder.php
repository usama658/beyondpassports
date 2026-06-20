<?php

namespace Database\Seeders;

use App\Enums\SupplyNodeType;
use App\Models\Destination;
use App\Models\SupplyNode;
use Illuminate\Database\Seeder;

/**
 * Seeds a small set of representative supply nodes (centres, couriers,
 * PayPoint, embassy) plus a couple of destination links.
 *
 * Idempotent: keyed on `node_key` via updateOrCreate. Pivot links use sync()
 * so re-running does not duplicate.
 *
 * PLACEHOLDER data — contact details and SLAs are illustrative and must be
 * confirmed against live partner agreements before launch.
 */
class SupplyNodeSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            [
                'node_key' => 'paypoint-uk-idp',
                'type' => SupplyNodeType::Paypoint,
                'name' => 'PayPoint (UK IDP issuer)',
                'contact' => 'https://www.paypoint.com/',
                'sla' => 'In-person, same-day issue at participating stores',
                'notes' => 'UK International Driving Permit issuer (guided self-service, in person only).',
                'is_global' => true,
                'destinations' => [],
            ],
            [
                'node_key' => 'courier-royal-mail-special',
                'type' => SupplyNodeType::Courier,
                'name' => 'Royal Mail Special Delivery',
                'contact' => 'https://www.royalmail.com/special-delivery',
                'sla' => 'Next working day by 1pm, tracked & signed',
                'notes' => 'Default tracked courier for outbound document return.',
                'is_global' => true,
                'destinations' => [],
            ],
            [
                'node_key' => 'centre-vfs-london',
                'type' => SupplyNodeType::Centre,
                'name' => 'VFS Global Visa Application Centre — London',
                'contact' => 'https://www.vfsglobal.com/',
                'sla' => 'Appointment-based; standard 5–10 working days',
                'notes' => 'Biometrics / document submission centre.',
                'is_global' => false,
                'postcode' => 'WC2N 5DU', 'lat' => 51.5074, 'lng' => -0.1278, 'we_book_here' => true,
                'destinations' => ['india'],
            ],
            [
                'node_key' => 'centre-vfs-manchester',
                'type' => SupplyNodeType::Centre,
                'name' => 'VFS Global Visa Application Centre — Manchester',
                'contact' => 'https://www.vfsglobal.com/',
                'sla' => 'Appointment-based; standard 5–10 working days',
                'notes' => 'Biometrics / document submission centre.',
                'is_global' => false,
                'postcode' => 'M1 2WD', 'lat' => 53.4808, 'lng' => -2.2426, 'we_book_here' => true,
                'destinations' => ['india'],
            ],
            [
                'node_key' => 'centre-vfs-birmingham',
                'type' => SupplyNodeType::Centre,
                'name' => 'VFS Global Visa Application Centre — Birmingham',
                'contact' => 'https://www.vfsglobal.com/',
                'sla' => 'Appointment-based; standard 5–10 working days',
                'notes' => 'Biometrics / document submission centre.',
                'is_global' => false,
                'postcode' => 'B2 4QA', 'lat' => 52.4862, 'lng' => -1.8904, 'we_book_here' => true,
                'destinations' => ['india'],
            ],
            [
                'node_key' => 'paypoint-london',
                'type' => SupplyNodeType::Paypoint,
                'name' => 'PayPoint IDP counter — Central London',
                'contact' => 'https://www.paypoint.com/',
                'sla' => 'In-person, same-day issue',
                'notes' => 'International Driving Permit issued in person (guided self-service).',
                'is_global' => false,
                'postcode' => 'EC1A 1BB', 'lat' => 51.5190, 'lng' => -0.0980, 'we_book_here' => true,
                'destinations' => [],
            ],
            [
                'node_key' => 'paypoint-edinburgh',
                'type' => SupplyNodeType::Paypoint,
                'name' => 'PayPoint IDP counter — Edinburgh',
                'contact' => 'https://www.paypoint.com/',
                'sla' => 'In-person, same-day issue',
                'notes' => 'International Driving Permit issued in person (guided self-service).',
                'is_global' => false,
                'postcode' => 'EH1 1BB', 'lat' => 55.9533, 'lng' => -3.1883, 'we_book_here' => true,
                'destinations' => [],
            ],
            [
                'node_key' => 'embassy-egypt-london',
                'type' => SupplyNodeType::Embassy,
                'name' => 'Egyptian Consulate — London',
                'contact' => 'https://www.egyptembassy.org.uk/',
                'sla' => 'Postal & in-person; varies by service',
                'notes' => 'Fallback for cases not eligible for the Egypt eVisa.',
                'is_global' => false,
                'postcode' => 'W2 1AY', 'lat' => 51.5145, 'lng' => -0.1790, 'we_book_here' => false,
                'destinations' => ['egypt'],
            ],
        ];

        foreach ($nodes as $data) {
            $slugs = $data['destinations'];
            unset($data['destinations']);

            $node = SupplyNode::updateOrCreate(
                ['node_key' => $data['node_key']],
                $data
            );

            if (! $node->is_global && $slugs !== []) {
                $ids = Destination::whereIn('slug', $slugs)->pluck('id')->all();
                $node->destinations()->sync($ids);
            }
        }
    }
}
