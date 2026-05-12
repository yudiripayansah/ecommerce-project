<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Observers\OrderObserver;
use App\Observers\ProductVariantObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);

        try {
            $name = Setting::get('store_name');
            if ($name) {
                config(['app.name' => $name]);
            }
        } catch (\Throwable) {
            // Settings table may not exist yet (e.g. during migrations)
        }
    }
}
