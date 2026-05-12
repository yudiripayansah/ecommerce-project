<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $attributes = [
        'plan'      => 'free',
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'plan', 'is_active'];
    }
}
