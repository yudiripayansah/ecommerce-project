<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection as ProductCollection;
use App\Models\Product;
use App\Models\StoreFile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollectionResource extends Resource
{
    protected static ?string $model = ProductCollection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Collections';

    protected static ?string $modelLabel = 'Collection';

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

                        RichEditor::make('description')
                            ->label('Description')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('store-files/' . now()->format('Y/m'))
                            ->hintActions([
                                Action::make('insert_image_desc')
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

                // ── Products ─────────────────────────────────────────────────
                Section::make('Products')
                    ->schema([
                        Select::make('products')
                            ->label(false)
                            ->multiple()
                            ->relationship('products', 'title')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Product::where('title', 'like', "%{$search}%")
                                    ->orWhere('handle', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function (Product $p): array {
                                        $badge = match ($p->status) {
                                            'active'   => '✓',
                                            'archived' => '⊗',
                                            default    => '○',
                                        };
                                        return [$p->id => $badge . ' ' . $p->title];
                                    })
                                    ->all();
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return Product::whereIn('id', $values)
                                    ->get()
                                    ->mapWithKeys(function (Product $p): array {
                                        $badge = match ($p->status) {
                                            'active'   => '✓',
                                            'archived' => '⊗',
                                            default    => '○',
                                        };
                                        return [$p->id => $badge . ' ' . $p->title];
                                    })
                                    ->all();
                            })
                            ->placeholder('Search products…')
                            ->helperText('Type to search and add products to this collection.'),
                    ]),

                // ── SEO ──────────────────────────────────────────────────────
                Section::make('Search engine listing')
                    ->description('Control how this collection appears in search results.')
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
                            ->prefix('/collections/')
                            ->required()
                            ->unique(ProductCollection::class, 'handle', ignoreRecord: true)
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->helperText('The URL for this collection: /collections/{handle}'),
                    ]),

            ])->columnSpan(2),

            // ── Sidebar (1/3) ────────────────────────────────────────────────
            Group::make([

                // ── Image ─────────────────────────────────────────────────────
                Section::make('Image')
                    ->headerActions([
                        Action::make('select_image')
                            ->label(fn (Get $get): string => $get('store_file_id') ? 'Change image' : 'Select image')
                            ->icon('heroicon-o-photo')
                            ->modalHeading('Select image')
                            ->modalWidth('4xl')
                            ->schema([
                                Tabs::make()
                                    ->tabs([
                                        Tab::make('Upload')
                                            ->icon('heroicon-o-arrow-up-tray')
                                            ->schema([
                                                FileUpload::make('upload_file')
                                                    ->label(false)
                                                    ->disk('public')
                                                    ->directory('store-files/' . now()->format('Y/m'))
                                                    ->visibility('public')
                                                    ->image()
                                                    ->acceptedFileTypes([
                                                        'image/jpeg', 'image/jpg', 'image/png',
                                                        'image/webp', 'image/gif', 'image/svg+xml',
                                                    ])
                                                    ->storeFileNamesIn('upload_original_name')
                                                    ->maxSize(10_240)
                                                    ->imagePreviewHeight('200')
                                                    ->helperText('Images only · Max 10 MB'),
                                            ]),

                                        Tab::make('From Files library')
                                            ->icon('heroicon-o-folder-open')
                                            ->schema([
                                                CheckboxList::make('file_id')
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
                                                    ->columns(2)
                                                    ->gridDirection('row'),
                                            ]),
                                    ]),
                            ])
                            ->action(function (array $data, Set $set): void {
                                $file = null;

                                if (! empty($data['upload_file'])) {
                                    $path = $data['upload_file'];
                                    $file = StoreFile::firstOrCreate(
                                        ['path' => $path, 'disk' => 'public'],
                                        static::buildStoreFileAttributes(
                                            $path,
                                            isset($data['upload_original_name'][$path])
                                                ? $data['upload_original_name'][$path]
                                                : basename($path)
                                        )
                                    );
                                } elseif (! empty($data['file_id'])) {
                                    $id   = is_array($data['file_id']) ? reset($data['file_id']) : $data['file_id'];
                                    $file = StoreFile::find($id);
                                }

                                if ($file) {
                                    $set('store_file_id', $file->id);
                                    $set('_image_preview_html', static::makeImagePreviewHtml($file));
                                }
                            }),

                        Action::make('remove_image')
                            ->label('Remove')
                            ->color('danger')
                            ->icon('heroicon-o-trash')
                            ->visible(fn (Get $get): bool => (bool) $get('store_file_id'))
                            ->requiresConfirmation()
                            ->action(function (Set $set): void {
                                $set('store_file_id', null);
                                $set('_image_preview_html', null);
                            }),
                    ])
                    ->schema([
                        Hidden::make('store_file_id'),

                        ViewField::make('_image_preview_html')
                            ->view('filament.components.media-preview')
                            ->visible(fn (Get $get): bool => (bool) $get('store_file_id')),
                    ]),

                // ── Publishing ────────────────────────────────────────────────
                Section::make('Publishing')
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Published at')
                            ->hint('Leave empty to keep hidden'),
                    ]),

                // ── Sort Order ────────────────────────────────────────────────
                Section::make('Sort order')
                    ->schema([
                        Select::make('sort_order')
                            ->label(false)
                            ->options([
                                'manual'             => 'Manual',
                                'best-selling'       => 'Best Selling',
                                'title-ascending'    => 'Title A–Z',
                                'title-descending'   => 'Title Z–A',
                                'price-ascending'    => 'Price Low–High',
                                'price-descending'   => 'Price High–Low',
                                'created-ascending'  => 'Oldest First',
                                'created-descending' => 'Newest First',
                            ])
                            ->default('manual')
                            ->required(),
                    ]),

            ])->columnSpan(1),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('collection_image_url')
                    ->label('')
                    ->size(50)
                    ->getStateUsing(fn ($record) => $record->storeFile
                        ? url('storage/' . $record->storeFile->path)
                        : null
                    ),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('handle')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->alignCenter(),

                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Unpublished'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('storeFile');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit'   => Pages\EditCollection::route('/{record}/edit'),
        ];
    }

    public static function makeImagePreviewHtml(StoreFile $file): string
    {
        $src = parse_url($file->url, PHP_URL_PATH) ?? '/storage/' . $file->path;

        return '<img src="' . e($src) . '" alt="' . e($file->alt ?? $file->filename) . '" '
             . 'style="width:100%;height:200px;object-fit:contain;display:block;" />';
    }

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
