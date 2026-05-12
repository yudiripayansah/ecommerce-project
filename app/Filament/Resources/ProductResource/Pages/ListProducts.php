<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Imports\ProductsImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Import Excel ─────────────────────────────────────────────
            Action::make('importProducts')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Products dari Excel')
                ->modalDescription('Upload file Excel (.xlsx / .xls) sesuai format template. Produk baru akan ditambahkan, bukan menimpa data lama.')
                ->modalWidth('lg')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->disk('local')
                        ->directory('imports/products')
                        ->required()
                        ->helperText('Format: .xlsx atau .xls — maks. 10 MB')
                        ->maxSize(10_240),
                ])
                ->action(function (array $data): void {
                    $path = storage_path('app/private/' . $data['file']);

                    $import = new ProductsImport();
                    Excel::import($import, $path);

                    $failures = $import->failures();
                    $errors   = $import->errors();
                    $imported = $import->importedCount;

                    if ($failures->isNotEmpty() || ! empty($errors)) {
                        $messages = $failures->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))->take(5)->toArray();

                        Notification::make()
                            ->title("Import selesai dengan {$failures->count()} baris gagal")
                            ->body(implode("\n", $messages))
                            ->warning()
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("{$imported} produk berhasil diimport")
                            ->success()
                            ->send();
                    }
                }),

            // ── Download Template ────────────────────────────────────────
            Action::make('downloadTemplate')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('admin.products.import-template'))
                ->openUrlInNewTab(),

            // ── Export Excel ─────────────────────────────────────────────
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(route('admin.products.export-excel'))
                ->openUrlInNewTab(),

            // ── Export PDF ───────────────────────────────────────────────
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->url(route('admin.products.export-pdf'))
                ->openUrlInNewTab(),

            CreateAction::make(),
        ];
    }
}
