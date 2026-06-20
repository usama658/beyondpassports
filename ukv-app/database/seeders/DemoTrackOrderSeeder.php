<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Seeder;

/**
 * One demo order with a known, shareable reference so the public tracker
 * (/track) can be demonstrated. NOT wired into ProductionSeeder — run manually:
 *
 *   php artisan db:seed --class=DemoTrackOrderSeeder --force
 *
 * Idempotent (updateOrCreate on order_ref). Mid-pipeline status so the timeline
 * shows real progress. No real customer PII — clearly a demo record.
 */
class DemoTrackOrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::updateOrCreate(
            ['order_ref' => 'UKV-2026-100200'],
            [
                'name'             => 'Demo Traveller',
                'email'            => 'demo@beyondpassports.co.uk',
                'destination_name' => 'Turkey',
                'tier'             => 'standard',
                'service_fee'      => 49.00,
                'govt_fee'         => 0.00,
                'total'            => 49.00,
                'status'           => OrderStatus::DocReview,
                'paid_at'          => now()->subDays(2),
            ]
        );
    }
}
