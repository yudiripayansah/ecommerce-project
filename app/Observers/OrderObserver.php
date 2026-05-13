<?php

namespace App\Observers;

use App\Actions\Inventory\CancelStockReservationAction;
use App\Actions\Inventory\ReleaseStockReservationAction;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderObserver
{
    public function created(Order $order): void
    {
        $admins = User::all();

        if ($admins->isEmpty()) {
            return;
        }

        $paymentLabel = $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'COD';
        $total        = 'Rp ' . number_format($order->total, 0, ',', '.');

        $notification = Notification::make()
            ->title('Order baru masuk')
            ->body("#{$order->order_number} · {$order->customer_name} · {$total} · {$paymentLabel}")
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('Lihat Order')
                    ->url(route('filament.admin.resources.orders.view', $order->id))
                    ->button(),
            ]);

        foreach ($admins as $admin) {
            $admin->notifyNow($notification->toDatabase());
        }
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $previousStatus = $order->getOriginal('status');
        $newStatus      = $order->status;

        // Admin confirms bank-transfer payment: release reservation → deduct stock
        if ($newStatus === 'processing' && $order->payment_method === 'bank_transfer') {
            app(ReleaseStockReservationAction::class)->handle($order);
        }

        // Order cancelled after reservation was created: free reserved stock
        if ($newStatus === 'cancelled' && in_array($previousStatus, ['pending', 'pending_payment'])) {
            app(CancelStockReservationAction::class)->handle($order);
        }
    }
}
