<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    protected $model = Destination::class;

    public function definition(): array
    {
        $name = fake()->country();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'visa_type' => fake()->randomElement(['evisa', 'visa_on_arrival', 'no_visa']),
            'govt_fee_gbp' => 20.00,
            'tier_standard_gbp' => 35.00,
            'tier_express_gbp' => 55.00,
            'tier_premium_gbp' => 85.00,
            'passport_validity_months' => 6,
        ];
    }
}
