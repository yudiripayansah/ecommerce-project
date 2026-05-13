<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use Tests\TenantTestCase;

class CartTest extends TenantTestCase
{
    // ── Add to cart ───────────────────────────────────────────────────────────

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();

        $response = $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 1,
        ]);

        $response->assertRedirect();
        $this->assertNotEmpty(session('cart'));
    }

    public function test_cart_stores_correct_price_from_db(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 75000]);

        $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 1,
        ]);

        $cart = session('cart');
        $key  = "{$product->id}-{$variant->id}";

        $this->assertEquals(75000, $cart[$key]['price']);
    }

    public function test_adding_same_item_twice_accumulates_quantity(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->withStock(20)->create();

        $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 2,
        ]);

        $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 3,
        ]);

        $key = "{$product->id}-{$variant->id}";
        $this->assertEquals(5, session('cart')[$key]['quantity']);
    }

    public function test_cannot_add_out_of_stock_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->outOfStock()->create();

        $response = $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 1,
        ]);

        $response->assertSessionHasErrors('stock');
        $this->assertEmpty(session('cart', []));
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->withStock(3)->create();

        $response = $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 5,
        ]);

        $response->assertSessionHasErrors('stock');
    }

    public function test_cannot_exceed_stock_when_accumulating_quantities(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->withStock(3)->create();

        $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 2,
        ]);

        $response = $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 2,
        ]);

        $response->assertSessionHasErrors('stock');

        $key = "{$product->id}-{$variant->id}";
        $this->assertEquals(2, session('cart')[$key]['quantity']);
    }

    public function test_product_without_stock_tracking_can_always_be_added(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create([
            'track_stock'        => false,
            'inventory_quantity' => 0,
        ]);

        $response = $this->tenantPost('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity'   => 99,
        ]);

        $response->assertSessionMissing('errors');
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function test_can_update_cart_item_quantity(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->withStock(10)->create();
        $key     = "{$product->id}-{$variant->id}";

        $response = $this->withSession(['cart' => [
            $key => $this->makeCartItem($product->id, $variant->id, quantity: 2),
        ]])->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post("/cart/update/{$key}", ['quantity' => 5]);

        $response->assertSessionHas("cart.{$key}.quantity", 5);
    }

    public function test_updating_quantity_to_zero_removes_item(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();
        $key     = "{$product->id}-{$variant->id}";

        $response = $this->withSession(['cart' => [
            $key => $this->makeCartItem($product->id, $variant->id),
        ]])->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post("/cart/update/{$key}", ['quantity' => 0]);

        $response->assertSessionMissing("cart.{$key}");
    }

    // ── Remove ────────────────────────────────────────────────────────────────

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();
        $key     = "{$product->id}-{$variant->id}";

        $response = $this->withSession(['cart' => [
            $key => $this->makeCartItem($product->id, $variant->id),
        ]])->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post("/cart/remove/{$key}");

        $response->assertSessionMissing("cart.{$key}");
    }

    public function test_can_clear_entire_cart(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();
        $key     = "{$product->id}-{$variant->id}";

        $this->withSession(['cart' => [
            $key => $this->makeCartItem($product->id, $variant->id),
        ]])->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/cart/clear');

        $this->assertEmpty(session('cart', []));
    }
}
