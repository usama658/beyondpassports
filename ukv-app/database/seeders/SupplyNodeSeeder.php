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
                'destinations' => ['india'],
            ],
            [
                'node_key' => 'embassy-egypt-london',
                'type' => SupplyNodeType::Embassy,
                'name' => 'Egyptian Consulate — London',
                'contact' => 'https://www.egyptembassy.org.uk/',
                'sla' => 'Postal & in-person; varies by service',
                'notes' => 'Fallback for cases not eligible for the Egypt eVisa.',
                'is_global' => false,
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
