<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class ReserveStockAction
{
    /**
     * Reserve stock for all items in an order.
     *
     * @param  'midtrans'|'bank_transfer'  $method  Determines reservation expiry window.
     */
    public function handle(Order $order, string $method): void
    {
        $expiresAt = match ($method) {
            'midtrans'      => now()->addHours(24),
            'bank_transfer' => now()->addDays(3),
            default         => now()->addHours(24),
        };

        foreach ($order->items as $item) {
            $this->reserveItem(
                variantId: $item->variant_id,
                productId: $item->product_id,
                orderId:   $order->id,
                quantity:  $item->quantity,
                expiresAt: $expiresAt,
            );
        }
    }

    private function reserveItem(
        ?int $variantId,
        ?int $productId,
        int $orderId,
        int $quantity,
        \Carbon\Carbon $expiresAt,
    ): void {
        $tracksStock = $variantId
            ? (bool) ProductVariant::find($variantId)?->track_stock
            : (bool) Product::find($productId)?->track_stock;

        if (! $tracksStock) {
            return;
        }

        DB::transaction(function () use ($variantId, $productId, $orderId, $quantity, $expiresAt) {
            $item = $variantId
                ? InventoryItem::forVariant($variantId)
                : InventoryItem::forProduct($productId);

            // Lock the row to prevent race conditions
            $item = InventoryItem::lockForUpdate()->find($item->id);
            $item->increment('quantity_reserved', $quantity);

            StockReservation::create([
                'inventory_item_id' => $item->id,
                'order_id'          => $orderId,
                'quantity'          => $quantity,
                'status'            => 'active',
                'expires_at'        => $expiresAt,
            ]);

            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'quantity'          => -$quantity, // blocked from available
                'type'              => 'reserve',
                'reference_type'    => Order::class,
                'reference_id'      => $orderId,
            ]);
        });
    }
}
