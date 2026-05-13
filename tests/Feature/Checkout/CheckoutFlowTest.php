<?php

namespace Tests\Feature\Checkout;

use App\Actions\Payment\CreatePaymentAction;
use App\Actions\Payment\PaymentResult;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Tests\TenantTestCase;

class CheckoutFlowTest extends TenantTestCase
{
    // ── Guards ────────────────────────────────────────────────────────────────

    public function test_empty_cart_redirects_to_cart_page(): void
    {
        $this->tenantPost('/checkout', $this->checkoutPayload())
            ->assertRedirect('/cart');
    }

    public function test_checkout_page_requires_non_empty_cart(): void
    {
        $this->tenantGet('/checkout')->assertRedirect('/cart');
    }

    // ── COD ───────────────────────────────────────────────────────────────────

    public function test_cod_checkout_creates_order_and_decrements_stock(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        $cart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, price: 50000, quantity: 2),
        ]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'cod']))
            ->assertRedirect('/checkout/success');

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'john@example.com',
            'payment_method' => 'cod',
            'status'         => 'pending',
            'subtotal'       => 100000,
            'total'          => 100000,
        ]);

        $this->assertEquals(3, $variant->fresh()->inventory_quantity);
    }

    public function test_cod_checkout_clears_cart_on_success(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload());

        $this->assertEmpty(session('cart', []));
    }

    public function test_cod_does_not_create_stock_reservation(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'cod']));

        $this->assertDatabaseCount('stock_reservations', 0);
    }

    // ── Bank transfer ─────────────────────────────────────────────────────────

    public function test_bank_transfer_checkout_reserves_stock(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(10)->create(['price' => 100000]);

        $cart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, price: 100000, quantity: 2),
        ]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'bank_transfer']))
            ->assertRedirect('/checkout/success');

        $this->assertDatabaseHas('stock_reservations', [
            'quantity' => 2,
            'status'   => 'active',
        ]);

        // Physical stock should NOT be decremented yet
        $this->assertEquals(10, $variant->fresh()->inventory_quantity);
    }

    public function test_bank_transfer_reservation_expires_in_3_days(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'bank_transfer']));

        $reservation = StockReservation::first();
        $this->assertNotNull($reservation);
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addDays(2)));
        $this->assertTrue($reservation->expires_at->lessThan(now()->addDays(4)));
    }

    // ── Midtrans ──────────────────────────────────────────────────────────────

    public function test_midtrans_checkout_returns_json_with_snap_token(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        $this->mock(CreatePaymentAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(PaymentResult::async('test-snap-token-abc123'));
        });

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $response = $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'midtrans']));

        $response->assertStatus(200)
            ->assertJsonFragment(['snap_token' => 'test-snap-token-abc123']);
    }

    public function test_midtrans_checkout_reserves_stock(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        $this->mock(CreatePaymentAction::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturn(PaymentResult::async('test-snap-token'));
        });

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id, quantity: 2)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'midtrans']));

        $this->assertDatabaseHas('stock_reservations', [
            'quantity' => 2,
            'status'   => 'active',
        ]);
        $this->assertEquals(5, $variant->fresh()->inventory_quantity);
    }

    public function test_midtrans_checkout_reserves_stock_for_24_hours(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        $this->mock(CreatePaymentAction::class, function ($mock) {
            $mock->shouldReceive('handle')->andReturn(PaymentResult::async('token'));
        });

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'midtrans']));

        $reservation = StockReservation::first();
        $this->assertTrue($reservation->expires_at->lessThan(now()->addHours(25)));
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addHours(23)));
    }

    // ── Price validation ──────────────────────────────────────────────────────

    public function test_checkout_uses_db_price_not_session_price(): void
    {
        $product = Product::factory()->create(['price' => 100000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 100000]);

        // Attacker tampers the session price to 1
        $cart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, price: 1, quantity: 1),
        ]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload());

        // Order should be created with REAL DB price, not the tampered session price
        $this->assertDatabaseHas('orders', ['subtotal' => 100000, 'total' => 100000]);
        $this->assertDatabaseMissing('orders', ['subtotal' => 1]);
    }

    // ── Stock validation ──────────────────────────────────────────────────────

    public function test_checkout_fails_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(1)->create(['price' => 50000]);

        $cart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, quantity: 5),
        ]);

        $response = $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload());

        $response->assertSessionHasErrors('stock');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_when_reserved_stock_reduces_availability(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(3)->create(['price' => 50000]);

        // First customer reserves 3 items
        $firstCart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, quantity: 3),
        ]);

        $this->withSession(['cart' => $firstCart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'bank_transfer']));

        // Second customer tries to buy 1 — but all 3 are reserved
        $secondCart = $this->makeCart([
            $this->makeCartItem($product->id, $variant->id, quantity: 1),
        ]);

        $response = $this->withSession(['cart' => $secondCart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['customer_email' => 'second@example.com']));

        $response->assertSessionHasErrors('stock');
        $this->assertDatabaseCount('orders', 1);
    }

    // ── Success page ──────────────────────────────────────────────────────────

    public function test_success_page_shows_order_details(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload());

        $order = Order::first();

        $this->withSession(['order_success' => $order->order_number])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->get('/checkout/success')
            ->assertStatus(200);
    }

    public function test_success_page_redirects_home_without_session(): void
    {
        $this->tenantGet('/checkout/success')->assertRedirect('/');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_checkout_requires_valid_payment_method(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $response = $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', $this->checkoutPayload(['payment_method' => 'bitcoin']));

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_checkout_requires_customer_details(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 50000]);

        $cart = $this->makeCart([$this->makeCartItem($product->id, $variant->id)]);

        $response = $this->withSession(['cart' => $cart])
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/checkout', []);

        $response->assertSessionHasErrors(['customer_name', 'customer_email', 'payment_method']);
    }
}
