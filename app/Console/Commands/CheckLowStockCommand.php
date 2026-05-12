<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use App\Models\User;
use App\Observers\ProductVariantObserver;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    protected $signature   = 'stock:check-low';
    protected $description = 'Kirim notifikasi untuk produk yang stoknya menipis atau habis';

    public function handle(): int
    {
        $admins = User::all();

        if ($admins->isEmpty()) {
            $this->info('Tidak ada admin user.');
            return self::SUCCESS;
        }

        $threshold = ProductVariantObserver::LOW_STOCK_THRESHOLD;

        $variants = ProductVariant::with('product')
            ->where('inventory_quantity', '<=', $threshold)
            ->get();

        if ($variants->isEmpty()) {
            $this->info('Semua produk stoknya aman.');
            return self::SUCCESS;
        }

        $outOfStock = $variants->where('inventory_quantity', 0);
        $lowStock   = $variants->where('inventory_quantity', '>', 0);

        if ($outOfStock->isNotEmpty()) {
            $items = $outOfStock->map(function ($v) {
                $name    = $v->product?->title ?? '—';
                $variant = filled($v->title) && $v->title !== 'Default Title' ? " ({$v->title})" : '';
                return "• {$name}{$variant}";
            })->join("\n");

            $notification = Notification::make()
                ->title("{$outOfStock->count()} produk stok habis")
                ->body($items)
                ->icon('heroicon-o-archive-box-x-mark')
                ->iconColor('danger')
                ->actions([
                    Action::make('view')
                        ->label('Lihat Produk')
                        ->url(route('filament.admin.resources.products.index'))
                        ->button(),
                ]);

            foreach ($admins as $admin) {
                $admin->notifyNow($notification->toDatabase());
            }
        }

        if ($lowStock->isNotEmpty()) {
            $items = $lowStock->map(function ($v) {
                $name    = $v->product?->title ?? '—';
                $variant = filled($v->title) && $v->title !== 'Default Title' ? " ({$v->title})" : '';
                return "• {$name}{$variant} — sisa {$v->inventory_quantity}";
            })->join("\n");

            $notification = Notification::make()
                ->title("{$lowStock->count()} produk stok menipis")
                ->body($items)
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('warning')
                ->actions([
                    Action::make('view')
                        ->label('Lihat Produk')
                        ->url(route('filament.admin.resources.products.index'))
                        ->button(),
                ]);

            foreach ($admins as $admin) {
                $admin->notifyNow($notification->toDatabase());
            }
        }

        $this->info("Notifikasi dikirim: {$outOfStock->count()} habis, {$lowStock->count()} menipis.");
        return self::SUCCESS;
    }
}
