<?php

namespace Tests\Feature\Inventory;

use App\Actions\Checkout\ValidateStockAction;
use App\Actions\Inventory\CancelStockReservationAction;
use App\Actions\Inventory\DecrementStockAction;
use App\Actions\Inventory\ReleaseStockReservationAction;
use App\Actions\Inventory\ReserveStockAction;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Tests\TenantTestCase;

class StockReservationTest extends TenantTestCase
{
    private function orderWithVariant(int $qty = 2, int $stock = 10): array
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock($stock)->create(['price' => 50000]);
        $order   = Order::factory()->create();

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'title'      => 'Test',
            'price'      => 50000,
            'quantity'   => $qty,
        ]);

        return compact('product', 'variant', 'order');
    }

    // ── Reserve ───────────────────────────────────────────────────────────────

    public function test_midtrans_reserve_creates_active_reservation_with_24h_expiry(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 2);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');

        $reservation = StockReservation::first();
        $this->assertNotNull($reservation);
        $this->assertEquals('active', $reservation->status);
        $this->assertEquals(2, $reservation->quantity);
        $this->assertTrue($reservation->expires_at->lessThan(now()->addHours(25)));
    }

    public function test_bank_transfer_reserve_expires_in_3_days(): void
    {
        ['order' => $order] = $this->orderWithVariant(qty: 1);

        app(ReserveStockAction::class)->handle($order->load('items'), 'bank_transfer');

        $reservation = StockReservation::first();
        $this->assertTrue($reservation->expires_at->greaterThan(now()->addDays(2)));
        $this->assertTrue($reservation->expires_at->lessThan(now()->addDays(4)));
    }

    public function test_reserve_increments_quantity_reserved_on_inventory_item(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 3);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');

        $inventoryItem = InventoryItem::where('variant_id', $variant->id)->first();
        $this->assertEquals(3, $inventoryItem->quantity_reserved);
    }

    public function test_reserve_creates_movement_of_type_reserve(): void
    {
        ['order' => $order] = $this->orderWithVariant(qty: 2);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');

        $this->assertDatabaseHas('inventory_movements', ['type' => 'reserve']);
    }

    public function test_reserve_does_not_decrement_physical_stock(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 2, stock: 10);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');

        $this->assertEquals(10, $variant->fresh()->inventory_quantity);
    }

    // ── Release ───────────────────────────────────────────────────────────────

    public function test_release_converts_reservation_to_actual_sale(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 2, stock: 10);

        app(ReserveStockAction::class)->handle($order->load('items'), 'bank_transfer');
        app(ReleaseStockReservationAction::class)->handle($order);

        // Physical stock decremented
        $this->assertEquals(8, $variant->fresh()->inventory_quantity);

        // Reservation marked released
        $this->assertEquals('released', StockReservation::first()->status);

        // quantity_reserved back to 0
        $inventoryItem = InventoryItem::where('variant_id', $variant->id)->first();
        $this->assertEquals(0, $inventoryItem->quantity_reserved);
    }

    public function test_release_creates_sale_movement(): void
    {
        ['order' => $order] = $this->orderWithVariant(qty: 1);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');
        app(ReleaseStockReservationAction::class)->handle($order);

        $this->assertDatabaseHas('inventory_movements', ['type' => 'sale']);
    }

    public function test_release_is_idempotent(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 2, stock: 10);

        app(ReserveStockAction::class)->handle($order->load('items'), 'bank_transfer');
        app(ReleaseStockReservationAction::class)->handle($order);
        app(ReleaseStockReservationAction::class)->handle($order); // Second call — no-op

        // Stock decremented only once
        $this->assertEquals(8, $variant->fresh()->inventory_quantity);
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function test_cancel_restores_reserved_quantity(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 3, stock: 10);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');
        app(CancelStockReservationAction::class)->handle($order);

        $inventoryItem = InventoryItem::where('variant_id', $variant->id)->first();
        $this->assertEquals(0, $inventoryItem->quantity_reserved);

        // Physical stock untouched
        $this->assertEquals(10, $variant->fresh()->inventory_quantity);
    }

    public function test_cancel_marks_reservation_as_cancelled(): void
    {
        ['order' => $order] = $this->orderWithVariant(qty: 1);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');
        app(CancelStockReservationAction::class)->handle($order);

        $this->assertEquals('cancelled', StockReservation::first()->status);
    }

    public function test_cancel_creates_reserve_cancelled_movement(): void
    {
        ['order' => $order] = $this->orderWithVariant(qty: 1);

        app(ReserveStockAction::class)->handle($order->load('items'), 'midtrans');
        app(CancelStockReservationAction::class)->handle($order);

        $this->assertDatabaseHas('inventory_movements', ['type' => 'reserve_cancelled']);
    }

    // ── COD (direct decrement, no reservation) ────────────────────────────────

    public function test_cod_decrement_reduces_physical_stock_immediately(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->orderWithVariant(qty: 3, stock: 10);

        $cart = [
            "{$variant->product_id}-{$variant->id}" => [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'quantity'   => 3,
                'price'      => 50000,
            ],
        ];

        app(DecrementStockAction::class)->handle($cart, $order);

        $this->assertEquals(7, $variant->fresh()->inventory_quantity);
        $this->assertDatabaseCount('stock_reservations', 0);
    }

    // ── ValidateStockAction with reservations ─────────────────────────────────

    public function test_validate_stock_accounts_for_reserved_items(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        // Reserve 3 items for another order
        $firstOrder = Order::factory()->create();
        OrderItem::create([
            'order_id'   => $firstOrder->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'title'      => 'Test',
            'price'      => 50000,
            'quantity'   => 3,
        ]);
        app(ReserveStockAction::class)->handle($firstOrder->load('items'), 'bank_transfer');

        // Available should now be 5 - 3 = 2
        $cart = [
            "{$product->id}-{$variant->id}" => [
                'product_id'    => $product->id,
                'variant_id'    => $variant->id,
                'title'         => 'Test',
                'variant_title' => null,
                'quantity'      => 3, // Requesting 3, but only 2 available
                'price'         => 50000,
            ],
        ];

        $error = app(ValidateStockAction::class)->handle($cart);

        $this->assertNotNull($error);
        $this->assertStringContainsString('tidak mencukupi', $error);
    }

    public function test_validate_stock_passes_when_enough_available_after_reservation(): void
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(5)->create(['price' => 50000]);

        // Reserve 2 items
        $firstOrder = Order::factory()->create();
        OrderItem::create([
            'order_id'   => $firstOrder->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'title'      => 'Test',
            'price'      => 50000,
            'quantity'   => 2,
        ]);
        app(ReserveStockAction::class)->handle($firstOrder->load('items'), 'midtrans');

        // Available = 5 - 2 = 3, requesting 3 — should pass
        $cart = [
            "{$product->id}-{$variant->id}" => [
                'product_id'    => $product->id,
                'variant_id'    => $variant->id,
                'title'         => 'Test',
                'variant_title' => null,
                'quantity'      => 3,
                'price'         => 50000,
            ],
        ];

        $error = app(ValidateStockAction::class)->handle($cart);

        $this->assertNull($error);
    }
}
