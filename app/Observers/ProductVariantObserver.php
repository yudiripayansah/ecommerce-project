<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ProductVariantObserver
{
    public const LOW_STOCK_THRESHOLD = 5;

    public function updated(ProductVariant $variant): void
    {
        if (! $variant->wasChanged('inventory_quantity')) {
            return;
        }

        $qty = (int) $variant->inventory_quantity;

        if ($qty > self::LOW_STOCK_THRESHOLD) {
            return;
        }

        $admins = User::all();

        if ($admins->isEmpty()) {
            return;
        }

        $product     = $variant->product;
        $title       = $product?->title ?? 'Produk';
        $variantInfo = filled($variant->title) && $variant->title !== 'Default Title'
            ? " ({$variant->title})"
            : '';

        $iconColor = $qty === 0 ? 'danger' : 'warning';
        $body      = $qty === 0
            ? "Stok {$title}{$variantInfo} telah habis."
            : "Stok {$title}{$variantInfo} tersisa {$qty} unit.";

        $notification = Notification::make()
            ->title($qty === 0 ? 'Stok habis!' : 'Stok menipis')
            ->body($body)
            ->icon('heroicon-o-archive-box-x-mark')
            ->iconColor($iconColor)
            ->actions([
                Action::make('edit')
                    ->label('Edit Produk')
                    ->url(route('filament.admin.resources.products.edit', $product?->id))
                    ->button(),
            ]);

        foreach ($admins as $admin) {
            $admin->notifyNow($notification->toDatabase());
        }
    }
}
