<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAddressFactory extends Factory
{
    protected $model = CustomerAddress::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name'        => fake()->name(),
            'phone'       => '08' . fake()->numerify('#########'),
            'address'     => fake()->streetAddress(),
            'city'        => 'Jakarta',
            'province'    => 'DKI Jakarta',
            'postal_code' => fake()->numerify('#####'),
            'country'     => 'Indonesia',
            'is_default'  => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
