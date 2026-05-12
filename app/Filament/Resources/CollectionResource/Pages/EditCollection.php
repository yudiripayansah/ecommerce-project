<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Models\StoreFile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['store_file_id'])) {
            $file = StoreFile::find($data['store_file_id']);
            if ($file) {
                $data['_image_preview_html'] = CollectionResource::makeImagePreviewHtml($file);
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['_image_preview_html']);

        if (! empty($data['store_file_id'])) {
            $file = StoreFile::find($data['store_file_id']);
            if ($file) {
                $data['image'] = $file->path;
            }
        } else {
            $data['image'] = null;
        }

        return $data;
    }
}
