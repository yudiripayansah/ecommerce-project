<?php

namespace Tests\Feature\Payment;

use App\Actions\Payment\HandleMidtransWebhookAction;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Tests\TenantTestCase;

class MidtransWebhookTest extends TenantTestCase
{
    private function makeOrderWithReservation(int $qty = 2): array
    {
        $product = Product::factory()->create(['price' => 50000]);
        $variant = ProductVariant::factory()->forProduct($product)->withStock(10)->create(['price' => 50000]);

        $order = Order::factory()->midtrans()->create(['customer_id' => null]);
        $order->forceFill(['customer_id' => null])->save();

        $item = OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'title'      => $product->title,
            'price'      => 50000,
            'quantity'   => $qty,
            'image'      => null,
        ]);

        $inventoryItem = InventoryItem::forVariant($variant->id);
        $inventoryItem->increment('quantity_reserved', $qty);

        StockReservation::create([
            'inventory_item_id' => $inventoryItem->id,
            'order_id'          => $order->id,
            'quantity'          => $qty,
            'status'            => 'active',
            'expires_at'        => now()->addHours(24),
        ]);

        return compact('order', 'variant', 'inventoryItem');
    }

    private function payload(Order $order, array $overrides = []): array
    {
        return array_merge([
            'transaction_status' => 'capture',
            'fraud_status'       => 'accept',
            'order_id'           => $order->order_number,
            'payment_type'       => 'credit_card',
            'transaction_id'     => 'TXN-' . uniqid(),
        ], $overrides);
    }

    // ── Success paths ─────────────────────────────────────────────────────────

    public function test_capture_accept_marks_order_as_processing(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'capture', 'fraud_status' => 'accept'])
        );

        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_settlement_marks_order_as_processing(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'settlement', 'fraud_status' => null])
        );

        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_payment_success_releases_reservation_and_decrements_stock(): void
    {
        ['order' => $order, 'variant' => $variant, 'inventoryItem' => $inventoryItem] =
            $this->makeOrderWithReservation(qty: 2);

        app(HandleMidtransWebhookAction::class)->handle($this->payload($order));

        // Physical stock decremented
        $this->assertEquals(8, $variant->fresh()->inventory_quantity);

        // Reservation released
        $reservation = StockReservation::where('order_id', $order->id)->first();
        $this->assertEquals('released', $reservation->status);

        // Reserved quantity back to 0
        $this->assertEquals(0, $inventoryItem->fresh()->quantity_reserved);
    }

    public function test_payment_success_saves_midtrans_transaction_details(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['payment_type' => 'gopay', 'transaction_id' => 'TXN-999'])
        );

        $fresh = $order->fresh();
        $this->assertEquals('TXN-999', $fresh->midtrans_transaction_id);
        $this->assertEquals('gopay', $fresh->midtrans_payment_type);
    }

    // ── Cancel paths ──────────────────────────────────────────────────────────

    public function test_cancel_marks_order_as_cancelled(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'cancel', 'fraud_status' => null])
        );

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_deny_marks_order_as_cancelled(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'deny', 'fraud_status' => null])
        );

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_expire_marks_order_as_cancelled(): void
    {
        ['order' => $order] = $this->makeOrderWithReservation();

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'expire', 'fraud_status' => null])
        );

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_cancel_releases_reserved_stock(): void
    {
        ['order' => $order, 'inventoryItem' => $inventoryItem] = $this->makeOrderWithReservation(qty: 3);

        app(HandleMidtransWebhookAction::class)->handle(
            $this->payload($order, ['transaction_status' => 'cancel', 'fraud_status' => null])
        );

        $this->assertEquals(0, $inventoryItem->fresh()->quantity_reserved);

        $reservation = StockReservation::where('order_id', $order->id)->first();
        $this->assertEquals('cancelled', $reservation->status);
    }

    // ── Idempotency ───────────────────────────────────────────────────────────

    public function test_duplicate_success_webhook_is_idempotent(): void
    {
        ['order' => $order, 'variant' => $variant] = $this->makeOrderWithReservation(qty: 2);

        $action  = app(HandleMidtransWebhookAction::class);
        $payload = $this->payload($order);

        $action->handle($payload);
        $action->handle($payload); // Second call — should be no-op

        // Stock decremented only once (10 - 2 = 8), not twice
        $this->assertEquals(8, $variant->fresh()->inventory_quantity);

        // Only one 'released' reservation, not two
        $this->assertDatabaseCount('stock_reservations', 1);
        $this->assertEquals('released', StockReservation::first()->status);
    }

    // ── Unknown order ─────────────────────────────────────────────────────────

    public function test_webhook_with_unknown_order_id_does_not_throw(): void
    {
        $action = app(HandleMidtransWebhookAction::class);

        // Should not throw — just logs a warning and returns
        $action->handle([
            'transaction_status' => 'settlement',
            'fraud_status'       => null,
            'order_id'           => 'ORD-DOESNOTEXIST',
            'payment_type'       => 'credit_card',
            'transaction_id'     => 'TXN-000',
        ]);

        $this->assertDatabaseCount('orders', 0);
    }
}
