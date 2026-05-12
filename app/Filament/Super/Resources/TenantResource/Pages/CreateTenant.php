<?php

namespace App\Filament\Super\Resources\TenantResource\Pages;

use App\Filament\Super\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove domain field — handled separately after tenant creation
        unset($data['domains']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $tenant = $this->record;

        // Create the domain entry (subdomain = tenant id by default)
        $subdomain = $tenant->id . '.' . env('CENTRAL_DOMAIN', 'localhost');
        $tenant->domains()->create(['domain' => $subdomain]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
