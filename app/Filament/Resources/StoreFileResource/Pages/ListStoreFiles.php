<?php

namespace App\Filament\Resources\StoreFileResource\Pages;

use App\Filament\Resources\StoreFileResource;
use App\Models\StoreFile;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ListStoreFiles extends ListRecords
{
    protected static string $resource = StoreFileResource::class;

    private function resolveUniqueFilePath(FilesystemAdapter $disk, string $dir, string $filename): string
    {
        $ext    = pathinfo($filename, PATHINFO_EXTENSION);
        $base   = $ext !== '' ? substr($filename, 0, -(strlen($ext) + 1)) : $filename;
        $path   = $dir . '/' . $filename;

        if (! $disk->exists($path)) {
            return $path;
        }

        $counter = 1;
        do {
            $candidate = $ext !== ''
                ? "{$dir}/{$base} ({$counter}).{$ext}"
                : "{$dir}/{$base} ({$counter})";
            $counter++;
        } while ($disk->exists($candidate));

        return $candidate;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload files')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Files')
                ->modalWidth('2xl')
                ->schema([
                    FileUpload::make('files')
                        ->label('Select files to upload')
                        ->multiple()
                        ->disk('public')
                        ->directory('store-files/' . now()->format('Y/m'))
                        ->visibility('public')
                        ->acceptedFileTypes([
                            // Images
                            'image/jpeg', 'image/png', 'image/gif',
                            'image/webp', 'image/svg+xml', 'image/avif',
                            // Videos
                            'video/mp4', 'video/quicktime', 'video/webm',
                            // Documents
                            'application/pdf',
                        ])
                        ->maxSize(51_200) // 50 MB per file
                        ->storeFileNamesIn('original_names')
                        ->panelLayout('grid')
                        ->imagePreviewHeight('120')
                        ->helperText('Accepted: JPEG, PNG, GIF, WebP, SVG, AVIF, MP4, MOV, WebM, PDF · Max 50 MB per file'),
                ])
                ->action(function (array $data): void {
                    $disk = Storage::disk('public');

                    foreach ($data['files'] as $tmpPath) {
                        $originalName = $data['original_names'][$tmpPath] ?? basename($tmpPath);
                        $dir          = dirname($tmpPath);

                        $targetPath = $this->resolveUniqueFilePath($disk, $dir, $originalName);

                        if ($tmpPath !== $targetPath) {
                            $disk->move($tmpPath, $targetPath);
                        }

                        StoreFile::create([
                            'filename'  => basename($targetPath),
                            'disk'      => 'public',
                            'path'      => $targetPath,
                            'url'       => $disk->url($targetPath),
                            'mime_type' => $disk->mimeType($targetPath),
                            'size'      => $disk->size($targetPath),
                        ]);
                    }

                    $count = count($data['files']);

                    Notification::make()
                        ->title($count === 1 ? '1 file uploaded' : "{$count} files uploaded")
                        ->success()
                        ->send();
                }),
        ];
    }
}
