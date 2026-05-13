<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class ReleaseStockReservationAction
{
    /**
     * Confirm stock sale: lift reservation and decrement physical stock.
     * Called on payment success (Midtrans webhook settlement / admin bank-transfer confirm).
     * Idempotent: safe to call twice.
     */
    public function handle(Order $order): void
    {
        $reservations = StockReservation::with('inventoryItem')
            ->where('order_id', $order->id)
            ->where('status', 'active')
            ->get();

        foreach ($reservations as $reservation) {
            DB::transaction(function () use ($reservation, $order) {
                $item = InventoryItem::lockForUpdate()->find($reservation->inventory_item_id);

                // Lift the reservation
                $item->decrement('quantity_reserved', $reservation->quantity);
                $reservation->update(['status' => 'released']);

                // Decrement physical stock (actual sale)
                if ($item->variant_id) {
                    $item->variant?->decrementStock($reservation->quantity);
                } elseif ($item->product_id) {
                    $item->product?->decrement('inventory_quantity', $reservation->quantity);
                }

                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'quantity'          => -$reservation->quantity,
                    'type'              => 'sale',
                    'reference_type'    => Order::class,
                    'reference_id'      => $order->id,
                ]);
            });
        }
    }
}
