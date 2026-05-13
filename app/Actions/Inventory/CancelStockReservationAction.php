<?php

namespace App\Actions\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class CancelStockReservationAction
{
    /**
     * Free reserved stock back to available pool.
     * Called on payment cancel, deny, expire, or reservation expiry sweep.
     * Idempotent: only processes active reservations.
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

                $item->decrement('quantity_reserved', $reservation->quantity);
                $reservation->update(['status' => 'cancelled']);

                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'quantity'          => +$reservation->quantity, // freed back
                    'type'              => 'reserve_cancelled',
                    'reference_type'    => Order::class,
                    'reference_id'      => $order->id,
                ]);
            });
        }
    }
}
