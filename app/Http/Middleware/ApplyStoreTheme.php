<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class ApplyStoreTheme
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (tenancy()->initialized) {
            $theme = Setting::get('theme', 'minimal');
            if ($theme !== 'minimal') {
                $path = resource_path("views/themes/{$theme}");
                if (is_dir($path)) {
                    view()->getFinder()->prependLocation($path);
                }
            }
        }

        return $next($request);
    }
}
