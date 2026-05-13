<?php

namespace App\Console\Commands;

use App\Actions\Inventory\CancelStockReservationAction;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Console\Command;

class ReleaseExpiredReservationsCommand extends Command
{
    protected $signature   = 'stock:release-expired';
    protected $description = 'Cancel expired stock reservations and mark orders as cancelled';

    public function handle(CancelStockReservationAction $cancel): void
    {
        $expiredOrderIds = StockReservation::where('status', 'active')
            ->where('expires_at', '<', now())
            ->distinct()
            ->pluck('order_id');

        if ($expiredOrderIds->isEmpty()) {
            $this->info('No expired reservations.');
            return;
        }

        foreach ($expiredOrderIds as $orderId) {
            $order = Order::find($orderId);

            if (! $order) {
                continue;
            }

            $cancel->handle($order);

            // Only cancel orders still waiting for payment
            if (in_array($order->status, ['pending', 'pending_payment'])) {
                $order->update(['status' => 'cancelled']);
            }
        }

        $this->info("Released reservations for {$expiredOrderIds->count()} order(s).");
    }
}
