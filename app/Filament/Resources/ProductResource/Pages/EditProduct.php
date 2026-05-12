<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\StoreFile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $pendingMedia = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->loadMissing('variants', 'media');

        // Reconstruct virtual productOptions Repeater from existing variant data
        $opt1Values = $record->variants->pluck('option1')->filter()->unique()->values()->toArray();
        $opt2Values = $record->variants->pluck('option2')->filter()->unique()->values()->toArray();
        $opt3Values = $record->variants->pluck('option3')->filter()->unique()->values()->toArray();

        $options = [];
        if (!empty($data['option1_name'])) {
            $options[] = ['name' => $data['option1_name'], 'values' => $opt1Values];
        }
        if (!empty($data['option2_name'])) {
            $options[] = ['name' => $data['option2_name'], 'values' => $opt2Values];
        }
        if (!empty($data['option3_name'])) {
            $options[] = ['name' => $data['option3_name'], 'values' => $opt3Values];
        }

        $data['productOptions'] = $options;

        // Populate the media Repeater from attached StoreFiles (ordered by pivot position)
        $data['media_attachments'] = $record->media
            ->map(fn ($file) => ProductResource::makeMediaItem($file))
            ->values()
            ->toArray();

        // Populate featured image preview
        if (! empty($data['featured_store_file_id'])) {
            $file = StoreFile::find($data['featured_store_file_id']);
            if ($file) {
                $data['_featured_preview_html'] = ProductResource::makeFeaturedImagePreviewHtml($file);
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingMedia = $data['media_attachments'] ?? [];
        unset($data['media_attachments'], $data['_featured_preview_html']);

        return $data;
    }

    protected function afterSave(): void
    {
        ProductResource::syncProductMedia($this->record, $this->pendingMedia);
    }
}
