<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant && ! $tenant->is_active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Toko ini sedang tidak aktif.'], 503);
            }

            return response()->view('errors.tenant-inactive', [], 503);
        }

        return $next($request);
    }
}
