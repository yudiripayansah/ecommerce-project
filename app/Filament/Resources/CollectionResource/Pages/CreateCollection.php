<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Models\StoreFile;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['_image_preview_html']);

        if (! empty($data['store_file_id'])) {
            $file = StoreFile::find($data['store_file_id']);
            if ($file) {
                $data['image'] = $file->path;
            }
        }

        return $data;
    }
}
