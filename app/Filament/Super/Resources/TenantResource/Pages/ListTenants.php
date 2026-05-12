<?php

namespace App\Filament\Super\Resources\TenantResource\Pages;

use App\Filament\Super\Resources\TenantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Toko'),
        ];
    }
}
