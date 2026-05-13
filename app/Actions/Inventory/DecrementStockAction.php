<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;

class DecrementStockAction
{
    /**
     * Immediately decrement physical stock (COD orders).
     *
     * @param  array<array{product_id: int|null, variant_id: int|null, quantity: int}>  $items
     * @param  Order|null  $order  Attached to movement records for audit trail.
     */
    public function handle(array $items, ?Order $order = null): void
    {
        foreach ($items as $item) {
            if ($item['variant_id']) {
                $variant = ProductVariant::find($item['variant_id']);
                if (! $variant) {
                    continue;
                }
                $variant->decrementStock($item['quantity']);

                if ($variant->track_stock) {
                    $inventoryItem = InventoryItem::forVariant($variant->id);
                    InventoryMovement::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'quantity'          => -$item['quantity'],
                        'type'              => 'sale',
                        'reference_type'    => $order ? Order::class : null,
                        'reference_id'      => $order?->id,
                    ]);
                }
            } else {
                $product = Product::find($item['product_id']);
                if (! $product || ! $product->track_stock) {
                    continue;
                }
                $product->decrement('inventory_quantity', $item['quantity']);

                $inventoryItem = InventoryItem::forProduct($product->id);
                InventoryMovement::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity'          => -$item['quantity'],
                    'type'              => 'sale',
                    'reference_type'    => $order ? Order::class : null,
                    'reference_id'      => $order?->id,
                ]);
            }
        }
    }
}
