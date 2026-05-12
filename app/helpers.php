<?php

if (! function_exists('rupiah')) {
    function rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (! function_exists('tenant_storage_prefix')) {
    /**
     * Tenant-scoped directory prefix for file uploads.
     * In tenant context  → "tenants/my-store/"
     * In central context → ""
     */
    function tenant_storage_prefix(): string
    {
        $id = tenant()?->getTenantKey();
        return $id ? "tenants/{$id}/" : '';
    }
}

if (! function_exists('store_name')) {
    function store_name(): string
    {
        return \App\Models\Setting::get('store_name', config('app.name', 'Store'));
    }
}

if (! function_exists('store_logo_url')) {
    function store_logo_url(): ?string
    {
        return \App\Models\Setting::get('store_logo') ?: null;
    }
}

if (! function_exists('store_favicon_url')) {
    function store_favicon_url(): ?string
    {
        return \App\Models\Setting::get('store_favicon') ?: null;
    }
}
