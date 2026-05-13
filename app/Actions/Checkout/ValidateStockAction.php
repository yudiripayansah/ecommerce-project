<?php

namespace App\Actions\Checkout;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;

class ValidateStockAction
{
    /**
     * Check available stock (on-hand minus reserved) for each cart item.
     * Returns an error string on the first shortage, or null if all items are OK.
     *
     * @param  array<string, array{product_id: int, variant_id: int|null, quantity: int, title: string, variant_title: string|null}>  $cart
     */
    public function handle(array $cart): ?string
    {
        foreach ($cart as $item) {
            if ($error = $this->checkItem($item)) {
                return $error;
            }
        }

        return null;
    }

    private function checkItem(array $item): ?string
    {
        if ($item['variant_id']) {
            $variant = ProductVariant::find($item['variant_id']);

            if (! $variant || ! $variant->track_stock) {
                return null;
            }

            $available = $this->available(
                onHand:   $variant->inventory_quantity,
                variantId: $variant->id,
            );

            if ($available < $item['quantity']) {
                $label = $item['title'] . ($item['variant_title'] ? " ({$item['variant_title']})" : '');
                return "Stok \"{$label}\" tidak mencukupi.";
            }

            return null;
        }

        $product = Product::find($item['product_id']);

        if (! $product || ! $product->track_stock) {
            return null;
        }

        $available = $this->available(
            onHand:    $product->inventory_quantity,
            productId: $product->id,
        );

        if ($available < $item['quantity']) {
            return "Stok \"{$item['title']}\" tidak mencukupi.";
        }

        return null;
    }

    private function available(int $onHand, ?int $variantId = null, ?int $productId = null): int
    {
        $reserved = 0;

        if ($variantId) {
            $inventoryItem = InventoryItem::where('variant_id', $variantId)->first();
            $reserved      = $inventoryItem?->quantity_reserved ?? 0;
        } elseif ($productId) {
            $inventoryItem = InventoryItem::where('product_id', $productId)->first();
            $reserved      = $inventoryItem?->quantity_reserved ?? 0;
        }

        return max(0, $onHand - $reserved);
    }
}
