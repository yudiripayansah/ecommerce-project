<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\Models\StoreFile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'Page';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([

            // ── Main content (2/3) ───────────────────────────────────────────
            Group::make([

                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                if (($get('handle') ?? '') !== Str::slug($old ?? '')) {
                                    return;
                                }
                                $set('handle', Str::slug($state));
                            }),

                        RichEditor::make('content')
                            ->label('Content')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('store-files/' . now()->format('Y/m'))
                            ->hintActions([
                                Action::make('insert_image')
                                    ->label('Insert image')
                                    ->icon('heroicon-o-photo')
                                    ->color('gray')
                                    ->modalHeading('Insert image')
                                    ->modalWidth('4xl')
                                    ->schema([
                                        Tabs::make()
                                            ->tabs([
                                                Tab::make('Upload')
                                                    ->icon('heroicon-o-arrow-up-tray')
                                                    ->schema([
                                                        FileUpload::make('files')
                                                            ->label(false)
                                                            ->multiple()
                                                            ->disk('public')
                                                            ->directory('store-files/' . now()->format('Y/m'))
                                                            ->visibility('public')
                                                            ->acceptedFileTypes([
                                                                'image/jpeg', 'image/jpg', 'image/png',
                                                                'image/webp', 'image/gif', 'image/svg+xml',
                                                            ])
                                                            ->storeFileNamesIn('original_names')
                                                            ->maxSize(10_240)
                                                            ->panelLayout('grid')
                                                            ->imagePreviewHeight('120')
                                                            ->helperText('Images only · Max 10 MB per file'),
                                                    ]),

                                                Tab::make('From Files library')
                                                    ->icon('heroicon-o-folder-open')
                                                    ->schema([
                                                        CheckboxList::make('file_ids')
                                                            ->label(false)
                                                            ->options(fn (): array => StoreFile::where('mime_type', 'like', 'image/%')
                                                                ->orderByDesc('created_at')
                                                                ->limit(200)
                                                                ->get()
                                                                ->mapWithKeys(function (StoreFile $f): array {
                                                                    $src   = e(parse_url($f->url, PHP_URL_PATH) ?? ('/storage/' . $f->path));
                                                                    $label = '<span style="display:flex;align-items:center;gap:10px;padding:4px 0;">'
                                                                           . '<img src="' . $src . '" style="width:56px;height:56px;object-fit:cover;border-radius:6px;flex-shrink:0;" />'
                                                                           . '<span style="display:flex;flex-direction:column;gap:2px;min-width:0;">'
                                                                           . '<span style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:140px;">' . e($f->filename) . '</span>'
                                                                           . '<span style="font-size:11px;color:#9ca3af;">' . e($f->meta) . '</span>'
                                                                           . '</span></span>';
                                                                    return [$f->id => $label];
                                                                })
                                                                ->all()
                                                            )
                                                            ->allowHtml()
                                                            ->searchable()
                                                            ->columns(3)
                                                            ->gridDirection('row'),
                                                    ]),
                                            ]),
                                    ])
                                    ->action(function (array $data, $component): void {
                                        $commands = [];

                                        foreach ($data['files'] ?? [] as $path) {
                                            $file = StoreFile::firstOrCreate(
                                                ['path' => $path, 'disk' => 'public'],
                                                static::buildStoreFileAttributes(
                                                    $path,
                                                    $data['original_names'][$path] ?? basename($path)
                                                )
                                            );
                                            $src        = parse_url($file->url, PHP_URL_PATH) ?? '/storage/' . $file->path;
                                            $commands[] = EditorCommand::make('insertContent', arguments: [[
                                                'type'  => 'image',
                                                'attrs' => ['src' => $src, 'alt' => $file->alt ?? $file->filename],
                                            ]]);
                                        }

                                        foreach (StoreFile::whereIn('id', $data['file_ids'] ?? [])->get() as $file) {
                                            $src        = parse_url($file->url, PHP_URL_PATH) ?? '/storage/' . $file->path;
                                            $commands[] = EditorCommand::make('insertContent', arguments: [[
                                                'type'  => 'image',
                                                'attrs' => ['src' => $src, 'alt' => $file->alt ?? $file->filename],
                                            ]]);
                                        }

                                        if (! empty($commands)) {
                                            $component->runCommands($commands);
                                        }
                                    }),
                            ]),
                    ]),

                // ── SEO ──────────────────────────────────────────────────────
                Section::make('Search engine listing')
                    ->description('Control how this page appears in search results.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Page title')
                            ->maxLength(70)
                            ->hint(fn (?string $state): string => strlen($state ?? '') . ' / 70')
                            ->live()
                            ->placeholder(fn (Get $get): string => $get('title') ?? ''),

                        Textarea::make('meta_description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(320)
                            ->hint(fn (?string $state): string => strlen($state ?? '') . ' / 320')
                            ->live(),

                        TextInput::make('handle')
                            ->label('URL handle')
                            ->prefix('/pages/')
                            ->required()
                            ->unique(Page::class, 'handle', ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->helperText('The URL for this page: /pages/{handle}'),
                    ]),

            ])->columnSpan(2),

            // ── Sidebar (1/3) ────────────────────────────────────────────────
            Group::make([

                Section::make('Visibility')
                    ->schema([
                        Select::make('visibility')
                            ->options([
                                'visible' => 'Visible',
                                'hidden'  => 'Hidden',
                            ])
                            ->default('hidden')
                            ->required()
                            ->live(),

                        DateTimePicker::make('published_at')
                            ->label('Published at')
                            ->visible(fn (Get $get): bool => $get('visibility') === 'visible')
                            ->hint('Leave empty to publish immediately'),
                    ]),

            ])->columnSpan(1),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('handle')
                    ->label('URL')
                    ->prefix('/pages/')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('visibility')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'visible' => 'success',
                        'hidden'  => 'gray',
                        default   => 'gray',
                    }),

                TextColumn::make('updated_at')
                    ->label('Last modified')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->options([
                        'visible' => 'Visible',
                        'hidden'  => 'Hidden',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    /** Build StoreFile attributes from a stored path (shared with ProductResource). */
    public static function buildStoreFileAttributes(string $path, ?string $originalName = null): array
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('public');
        $fullPath = $disk->path($path);
        $exists   = $disk->exists($path);

        return [
            'filename'  => $originalName ?? basename($path),
            'url'       => $disk->url($path),
            'mime_type' => ($exists && file_exists($fullPath))
                ? (mime_content_type($fullPath) ?: 'application/octet-stream')
                : 'application/octet-stream',
            'size' => $exists ? $disk->size($path) : 0,
        ];
    }
}
