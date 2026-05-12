<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $pendingMedia = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingMedia = $data['media_attachments'] ?? [];
        unset($data['media_attachments'], $data['_featured_preview_html']);

        return $data;
    }

    protected function afterCreate(): void
    {
        ProductResource::syncProductMedia($this->record, $this->pendingMedia);
    }
}
