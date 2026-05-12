<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Stancl\Tenancy\Tenancy;

class InitializeTenancyByDomainIfTenant
{
    public function __construct(
        private Tenancy $tenancy,
        private DomainTenantResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (in_array($request->getHost(), config('tenancy.central_domains', []), true)) {
            return $next($request);
        }

        if ($this->tenancy->initialized) {
            return $next($request);
        }

        try {
            $this->tenancy->initialize(
                $this->resolver->resolve($request->getHost())
            );
        } catch (TenantCouldNotBeIdentifiedException) {
            return $next($request);
        }

        return $next($request);
    }
}
