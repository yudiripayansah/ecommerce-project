<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreFileResource\Pages;
use App\Models\StoreFile;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StoreFileResource extends Resource
{
    protected static ?string $model = StoreFile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Files';

    protected static ?string $pluralModelLabel = 'Files';

    protected static ?string $modelLabel = 'File';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('preview_html')
                    ->label('Preview')
                    ->html()
                    ->extraAttributes(['style' => 'width:88px;padding:4px;']),

                TextColumn::make('filename')
                    ->label('File name')
                    ->limit(40)
                    ->tooltip(fn (StoreFile $record): string => $record->filename)
                    ->weight('medium')
                    ->description(fn (StoreFile $record): string => $record->meta)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->placeholder('All files')
                    ->options([
                        'image'    => 'Images',
                        'video'    => 'Videos',
                        'document' => 'Documents',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'image'    => $query->where('mime_type', 'like', 'image/%'),
                        'video'    => $query->where('mime_type', 'like', 'video/%'),
                        'document' => $query->where('mime_type', 'not like', 'image/%')
                                            ->where('mime_type', 'not like', 'video/%'),
                        default    => $query,
                    }),
            ])
            ->recordAction('preview')
            ->recordActions([
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->tooltip('Preview')
                    ->label('Preview')
                    ->color('gray')
                    ->modalHeading(fn (StoreFile $record): string => $record->filename)
                    ->modalContent(fn (StoreFile $record) => new \Illuminate\Support\HtmlString(
                        view('filament.modals.store-file-preview', compact('record'))->render()
                    ))
                    ->modalFooterActions([])
                    ->modalWidth('3xl'),

                Action::make('copy_url')
                    ->icon('heroicon-o-clipboard-document')
                    ->tooltip('Copy URL')
                    ->label('Copy URL')
                    ->color('gray')
                    ->action(function (StoreFile $record, $livewire): void {
                        $livewire->js('navigator.clipboard.writeText(' . json_encode($record->url) . ')');
                        Notification::make()
                            ->title('URL copied to clipboard')
                            ->success()
                            ->send();
                    }),

                // Edit alt text — images only
                Action::make('edit_alt')
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Edit alt text')
                    ->label('Edit alt text')
                    ->color('gray')
                    ->visible(fn (StoreFile $record): bool => $record->isImage())
                    ->fillForm(fn (StoreFile $record): array => ['alt' => $record->alt])
                    ->schema([
                        TextInput::make('alt')
                            ->label('Alt text')
                            ->placeholder('Describe the image for accessibility and SEO…')
                            ->helperText('Read by screen readers and shown when the image fails to load.'),
                    ])
                    ->action(fn (StoreFile $record, array $data): bool => $record->update(['alt' => $data['alt']]))
                    ->successNotificationTitle('Alt text updated'),

                DeleteAction::make()
                    ->tooltip('Delete')
                    ->label('Delete'),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateHeading('No files yet')
            ->emptyStateDescription('Upload images, videos, or documents to use across your store.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreFiles::route('/'),
        ];
    }
}
