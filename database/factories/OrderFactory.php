<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomElement([50000, 100000, 150000, 250000]);

        return [
            'customer_id'          => Customer::factory(),
            'order_number'         => 'ORD-' . strtoupper(Str::random(12)),
            'customer_name'        => fake()->name(),
            'customer_email'       => fake()->safeEmail(),
            'customer_phone'       => '08' . fake()->numerify('#########'),
            'shipping_address'     => fake()->streetAddress(),
            'shipping_city'        => 'Jakarta',
            'shipping_province'    => 'DKI Jakarta',
            'shipping_postal_code' => fake()->numerify('#####'),
            'payment_method'       => 'cod',
            'status'               => 'pending',
            'subtotal'             => $subtotal,
            'shipping_cost'        => 0,
            'total'                => $subtotal,
        ];
    }

    public function cod(): static
    {
        return $this->state(['payment_method' => 'cod', 'status' => 'pending']);
    }

    public function bankTransfer(): static
    {
        return $this->state(['payment_method' => 'bank_transfer', 'status' => 'pending']);
    }

    public function midtrans(): static
    {
        return $this->state(['payment_method' => 'midtrans', 'status' => 'pending']);
    }

    public function processing(): static
    {
        return $this->state(['status' => 'processing']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
