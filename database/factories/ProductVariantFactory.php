<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),
            'title'              => 'Default',
            'price'              => fake()->randomElement([50000, 75000, 100000, 150000]),
            'compare_at_price'   => null,
            'sku'                => strtoupper(fake()->unique()->bothify('SKU-######')),
            'inventory_quantity' => 10,
            'track_stock'        => false,
            'position'           => 1,
            'option1'            => null,
            'option2'            => null,
            'option3'            => null,
            'requires_shipping'  => true,
            'taxable'            => false,
        ];
    }

    public function withStock(int $quantity = 10): static
    {
        return $this->state([
            'track_stock'        => true,
            'inventory_quantity' => $quantity,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state([
            'track_stock'        => true,
            'inventory_quantity' => 0,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(['product_id' => $product->id]);
    }
}
