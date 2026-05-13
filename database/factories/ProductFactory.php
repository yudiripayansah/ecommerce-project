<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title'            => ucwords($title),
            'handle'           => Str::slug($title) . '-' . fake()->numerify('###'),
            'description'      => fake()->paragraph(),
            'price'            => fake()->randomElement([50000, 75000, 100000, 150000, 200000]),
            'compare_at_price' => null,
            'vendor'           => fake()->company(),
            'product_type'     => fake()->word(),
            'status'           => 'active',
            'track_stock'      => false,
            'inventory_quantity' => 0,
            'option1_name'     => null,
            'option2_name'     => null,
            'option3_name'     => null,
        ];
    }

    public function withStock(int $quantity = 10): static
    {
        return $this->state([
            'track_stock'        => true,
            'inventory_quantity' => $quantity,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
