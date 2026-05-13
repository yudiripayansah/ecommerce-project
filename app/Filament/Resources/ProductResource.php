<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([

            // ── Main content (2/3) ────────────────────────────────────────
            Group::make([

                Section::make('Product Details')
                    ->columns(2)
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

                        TextInput::make('handle')
                            ->required()
                            ->unique(Product::class, 'handle', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Auto-generated from title. Must be unique.')
                            ->rules(['alpha_dash']),

                        RichEditor::make('description')
                            ->columnSpanFull()
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
                                                            ->directory(tenant_storage_prefix() . 'store-files/' . now()->format('Y/m'))
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

                                        // New uploads → create StoreFile + insert
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

                                        // Picks from Files library → insert
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

                // ── Options (Shopify-style) ───────────────────────────────
                Section::make('Options')
                    ->description('Define options that customers can choose from, like size or color.')
                    ->schema([
                        Repeater::make('productOptions')
                            ->label('')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Option name')
                                        ->placeholder('e.g. Size')
                                        ->required()
                                        ->live(onBlur: true),
                                    TagsInput::make('values')
                                        ->label('Values')
                                        ->placeholder('Add a value, press Enter')
                                        ->live(),
                                ]),
                            ])
                            ->addActionLabel('Add option')
                            ->maxItems(3)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => static::generateVariants($get, $set))
                            ->dehydrated(false),

                        Hidden::make('option1_name'),
                        Hidden::make('option2_name'),
                        Hidden::make('option3_name'),
                    ]),

                // ── Variants (auto-generated from options) ────────────────
                Section::make('Variants')
                    ->visible(fn (Get $get): bool =>
                        collect($get('productOptions') ?? [])
                            ->contains(fn ($opt) => filled($opt['name'] ?? '') && !empty($opt['values'] ?? []))
                    )
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->extraAttributes(['style' => 'max-height: 420px; overflow-y: auto;'])
                            ->label('')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Variant')
                                    ->disabled()
                                    ->dehydrated(),

                                Grid::make(2)->schema([
                                    TextInput::make('price')
                                        ->label('Price')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->minValue(0)
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, Set $set, $livewire) {
                                            if (blank($state)) {
                                                $set('price', $livewire->data['price'] ?? null);
                                            }
                                        }),

                                    TextInput::make('compare_at_price')
                                        ->label('Compare at')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, Set $set, $livewire) {
                                            if (blank($state)) {
                                                $set('compare_at_price', $livewire->data['compare_at_price'] ?? null);
                                            }
                                        }),

                                    TextInput::make('sku')
                                        ->label('SKU'),

                                    TextInput::make('inventory_quantity')
                                        ->label('Stok')
                                        ->integer()
                                        ->default(0)
                                        ->visible(fn (Get $get) => (bool) $get('track_stock')),

                                    Toggle::make('track_stock')
                                        ->label('Lacak stok')
                                        ->default(false)
                                        ->live()
                                        ->columnSpanFull(),
                                ]),

                                // ── Variant image ─────────────────────────────────
                                TextInput::make('_image_filename')
                                    ->label('Gambar variant')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->placeholder('Opsional — tampil di cart & storefront')
                                    ->afterStateHydrated(function (Get $get, Set $set): void {
                                        $fileId = $get('store_file_id');
                                        if ($fileId) {
                                            $file = StoreFile::find($fileId);
                                            $set('_image_filename', $file?->filename);
                                        }
                                    })
                                    ->hintActions([
                                        Action::make('pick_variant_image')
                                            ->label('Pilih')
                                            ->icon('heroicon-o-photo')
                                            ->color('gray')
                                            ->modalHeading('Pilih gambar variant')
                                            ->modalWidth('4xl')
                                            ->schema([
                                                Tabs::make()->tabs([
                                                    Tab::make('Upload')
                                                        ->icon('heroicon-o-arrow-up-tray')
                                                        ->schema([
                                                            FileUpload::make('upload_file')
                                                                ->label(false)
                                                                ->disk('public')
                                                                ->directory(tenant_storage_prefix() . 'store-files/' . now()->format('Y/m'))
                                                                ->visibility('public')
                                                                ->image()
                                                                ->acceptedFileTypes([
                                                                    'image/jpeg', 'image/jpg', 'image/png',
                                                                    'image/webp', 'image/gif', 'image/svg+xml',
                                                                ])
                                                                ->storeFileNamesIn('upload_original_name')
                                                                ->maxSize(10_240)
                                                                ->imagePreviewHeight('160')
                                                                ->helperText('Gambar saja · Maks 10 MB'),
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
                                                                               . '<span style="font-size:13px;font-weight:500;">' . e($f->filename) . '</span>'
                                                                               . '</span>';
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
                                                            $data['upload_original_name'][$path] ?? basename($path)
                                                        )
                                                    );
                                                } elseif (! empty($data['file_id'])) {
                                                    $id   = is_array($data['file_id']) ? reset($data['file_id']) : $data['file_id'];
                                                    $file = StoreFile::find($id);
                                                }

                                                if ($file) {
                                                    $set('store_file_id', $file->id);
                                                    $set('_image_filename', $file->filename);
                                                }
                                            }),

                                        Action::make('remove_variant_image')
                                            ->label('Hapus')
                                            ->color('danger')
                                            ->icon('heroicon-o-trash')
                                            ->visible(fn (Get $get) => (bool) $get('store_file_id'))
                                            ->requiresConfirmation()
                                            ->action(function (Set $set): void {
                                                $set('store_file_id', null);
                                                $set('_image_filename', null);
                                            }),
                                    ]),

                                Hidden::make('store_file_id'),

                                // Hidden fields — managed by generateVariants()
                                Hidden::make('option1'),
                                Hidden::make('option2'),
                                Hidden::make('option3'),
                                Hidden::make('barcode'),
                                Hidden::make('weight'),
                                Hidden::make('weight_unit')->default('kg'),
                                Hidden::make('position'),
                                Hidden::make('requires_shipping')->default(true),
                                Hidden::make('taxable')->default(true),
                            ]),
                    ]),

                // ── Media ─────────────────────────────────────────────────
                Section::make('Media')
                    ->description('Files are saved to the Files library and can be reused across products.')
                    ->headerActions([
                        Action::make('add_media')
                            ->label('Add media')
                            ->icon('heroicon-o-photo')
                            ->color('gray')
                            ->modalHeading('Add media')
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
                                                    ->directory(tenant_storage_prefix() . 'store-files/' . now()->format('Y/m'))
                                                    ->visibility('public')
                                                    ->acceptedFileTypes([
                                                        'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                                                        'image/gif', 'image/svg+xml', 'image/avif',
                                                        'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime',
                                                    ])
                                                    ->storeFileNamesIn('original_names')
                                                    ->maxSize(51_200)
                                                    ->panelLayout('grid')
                                                    ->imagePreviewHeight('120')
                                                    ->helperText('Images & videos · Max 50 MB per file'),
                                            ]),

                                        Tab::make('From Files library')
                                            ->icon('heroicon-o-folder-open')
                                            ->schema([
                                                CheckboxList::make('file_ids')
                                                    ->label(false)
                                                    ->options(fn (): array => StoreFile::orderByDesc('created_at')
                                                        ->limit(200)
                                                        ->get()
                                                        ->mapWithKeys(function (StoreFile $f): array {
                                                            $src   = e(parse_url($f->url, PHP_URL_PATH) ?? ('/storage/' . $f->path));
                                                            $thumb = $f->isImage()
                                                                ? '<img src="' . $src . '" style="width:56px;height:56px;object-fit:cover;border-radius:6px;flex-shrink:0;" />'
                                                                : '<span style="width:56px;height:56px;border-radius:6px;background:#374151;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:10px;font-weight:600;color:#9ca3af;">'
                                                                  . e(strtoupper(pathinfo($f->filename, PATHINFO_EXTENSION) ?: 'FILE'))
                                                                  . '</span>';

                                                            $label = '<span style="display:flex;align-items:center;gap:10px;padding:4px 0;">'
                                                                   . $thumb
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
                            ->action(function (array $data, $get, $set): void {
                                $current     = array_values($get('media_attachments') ?? []);
                                $existingIds = array_column($current, 'store_file_id');

                                // Process new uploads
                                foreach ($data['files'] ?? [] as $path) {
                                    $file = StoreFile::firstOrCreate(
                                        ['path' => $path, 'disk' => 'public'],
                                        static::buildStoreFileAttributes(
                                            $path,
                                            $data['original_names'][$path] ?? basename($path)
                                        )
                                    );
                                    if (! in_array($file->id, $existingIds, strict: true)) {
                                        $current[]     = static::makeMediaItem($file);
                                        $existingIds[] = $file->id;
                                    }
                                }

                                // Process picks from Files library
                                foreach (StoreFile::whereIn('id', $data['file_ids'] ?? [])->get() as $file) {
                                    if (! in_array($file->id, $existingIds, strict: true)) {
                                        $current[]     = static::makeMediaItem($file);
                                        $existingIds[] = $file->id;
                                    }
                                }

                                $set('media_attachments', $current);
                            }),
                    ])
                    ->schema([
                        Repeater::make('media_attachments')
                            ->label('')
                            ->schema([
                                ViewField::make('_preview_html')
                                    ->label('')
                                    ->view('filament.components.media-preview')
                                    ->dehydrated(false),
                                Hidden::make('store_file_id'),
                                Hidden::make('_filename'),
                                TextInput::make('alt')
                                    ->label('Alt text')
                                    ->placeholder('Describe this media…'),
                            ])
                            ->defaultItems(0)
                            ->reorderable()
                            ->addable(false)
                            ->grid(3)
                            ->itemLabel(fn (array $state): string => $state['_filename'] ?? 'File')
                            ->collapsible()
                            ->visible(fn (Get $get): bool => !empty($get('media_attachments'))),
                    ]),

            ])->columnSpan(2),

            // ── Sidebar (1/3) ─────────────────────────────────────────────
            Group::make([

                // ── Featured image ────────────────────────────────────────
                Section::make('Featured image')
                    ->headerActions([
                        Action::make('select_featured_image')
                            ->label(fn (Get $get): string => $get('featured_store_file_id') ? 'Change image' : 'Select image')
                            ->icon('heroicon-o-photo')
                            ->modalHeading('Select featured image')
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
                                                    ->directory(tenant_storage_prefix() . 'store-files/' . now()->format('Y/m'))
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
                                    $set('featured_store_file_id', $file->id);
                                    $set('_featured_preview_html', static::makeFeaturedImagePreviewHtml($file));
                                }
                            }),

                        Action::make('remove_featured_image')
                            ->label('Remove')
                            ->color('danger')
                            ->icon('heroicon-o-trash')
                            ->visible(fn (Get $get): bool => (bool) $get('featured_store_file_id'))
                            ->requiresConfirmation()
                            ->action(function (Set $set): void {
                                $set('featured_store_file_id', null);
                                $set('_featured_preview_html', null);
                            }),
                    ])
                    ->schema([
                        Hidden::make('featured_store_file_id'),

                        ViewField::make('_featured_preview_html')
                            ->view('filament.components.media-preview')
                            ->visible(fn (Get $get): bool => (bool) $get('featured_store_file_id')),
                    ]),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->hint('Leave empty to keep as draft'),
                    ]),

                Section::make('Pricing')
                    ->description('Base price used when no variants are defined.')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),

                        TextInput::make('compare_at_price')
                            ->label('Compare at Price')
                            ->numeric()
                            ->prefix('Rp')
                            ->hint('Shown as crossed-out original price'),
                    ]),

                Section::make('Inventori')
                    ->description('Berlaku hanya jika produk tidak punya varian.')
                    ->schema([
                        Toggle::make('track_stock')
                            ->label('Lacak stok')
                            ->live()
                            ->default(false),

                        TextInput::make('inventory_quantity')
                            ->label('Jumlah stok')
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (Get $get) => (bool) $get('track_stock')),
                    ]),

                Section::make('Organization')
                    ->schema([
                        TextInput::make('vendor')
                            ->maxLength(255),

                        TextInput::make('product_type')
                            ->label('Product Type')
                            ->maxLength(255),

                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique('categories', 'slug'),
                            ]),

                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->unique('tags', 'name'),
                            ]),

                        Select::make('collections')
                            ->relationship('collections', 'title')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),

            ])->columnSpan(1),

        ]);
    }

    public static function makeFeaturedImagePreviewHtml(StoreFile $file): string
    {
        $src = parse_url($file->url, PHP_URL_PATH) ?? '/storage/' . $file->path;

        return '<img src="' . e($src) . '" alt="' . e($file->alt ?? $file->filename) . '" '
             . 'style="width:100%;height:200px;object-fit:contain;display:block;" loading="lazy" />';
    }

    /**
     * Sync product media from the media_attachments Repeater state.
     * Each item must contain at least a 'store_file_id' key.
     */
    public static function syncProductMedia(Product $product, array $attachments): void
    {
        $sync = [];

        foreach (array_values($attachments) as $position => $item) {
            $id = (int) ($item['store_file_id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $sync[$id] = ['position' => $position + 1];
        }

        $product->media()->sync($sync);
    }

    /** Build the attribute array for a new StoreFile record from a stored path. */
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

    /** Build a single Repeater item array from a StoreFile model. */
    public static function makeMediaItem(StoreFile $file): array
    {
        $src = e(parse_url($file->url, PHP_URL_PATH) ?? ('/storage/' . $file->path));

        if ($file->isImage()) {
            $preview = '<img src="' . $src . '" alt="' . e($file->alt ?? $file->filename) . '" '
                     . 'style="width:100%;height:200px;object-fit:contain;display:block;" loading="lazy" />';
        } elseif ($file->isVideo()) {
            $ext = strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION) ?: 'VIDEO');
            $preview = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:100%;height:200px;">'
                     . '<svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;color:#60a5fa;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>'
                     . '<span style="font-size:11px;font-weight:600;color:#9ca3af;">' . $ext . '</span>'
                     . '</div>';
        } else {
            $ext = strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION) ?: 'FILE');
            $preview = '<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;width:100%;height:200px;">'
                     . '<svg xmlns="http://www.w3.org/2000/svg" style="width:40px;height:40px;color:#9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>'
                     . '<span style="font-size:11px;font-weight:600;color:#9ca3af;">' . $ext . '</span>'
                     . '</div>';
        }

        return [
            'store_file_id' => $file->id,
            '_filename'     => $file->filename,
            '_preview_html' => $preview,
            'alt'           => $file->alt ?? '',
        ];
    }

    /**
     * Generate all variant combinations from the productOptions Repeater.
     * Preserves existing variant data (price, SKU, stock) for combinations that already exist.
     */
    protected static function generateVariants(Get $get, Set $set): void
    {
        $productOptions = $get('productOptions') ?? [];

        // Collect non-empty option groups and sync option names to hidden fields
        $optionGroups = [];
        $optionNames  = [];
        foreach ($productOptions as $opt) {
            $name   = trim($opt['name'] ?? '');
            $values = array_values(array_filter($opt['values'] ?? []));
            if ($name !== '' && !empty($values)) {
                $optionGroups[] = $values;
                $optionNames[]  = $name;
            }
        }

        $set('option1_name', $optionNames[0] ?? null);
        $set('option2_name', $optionNames[1] ?? null);
        $set('option3_name', $optionNames[2] ?? null);

        if (empty($optionGroups)) {
            $set('variants', []);
            return;
        }

        // Cartesian product to get all combinations
        $combinations = [[]];
        foreach ($optionGroups as $group) {
            $next = [];
            foreach ($combinations as $existing) {
                foreach ($group as $value) {
                    $next[] = [...$existing, $value];
                }
            }
            $combinations = $next;
        }

        // Current Repeater state (preserves existing user-entered data)
        $currentVariants = $get('variants') ?? [];

        $matchedItems = [];
        $newItems     = [];
        $position     = 1;

        foreach ($combinations as $combo) {
            $opt1  = $combo[0] ?? null;
            $opt2  = $combo[1] ?? null;
            $opt3  = $combo[2] ?? null;
            $title = implode(' / ', array_filter([$opt1, $opt2, $opt3]));

            // Find an existing variant with the same option values (to preserve its data)
            $matchedKey  = null;
            $matchedData = [];
            foreach ($currentVariants as $key => $variant) {
                if (
                    ($variant['option1'] ?? null) === $opt1 &&
                    ($variant['option2'] ?? null) === $opt2 &&
                    ($variant['option3'] ?? null) === $opt3
                ) {
                    $matchedKey  = $key;
                    $matchedData = $variant;
                    break;
                }
            }

            $item = array_merge(
                [
                    'price'              => $get('price') ?? 0,
                    'compare_at_price'   => $get('compare_at_price') ?: null,
                    'sku'                => null,
                    'barcode'            => null,
                    'store_file_id'      => null,
                    '_image_filename'    => null,
                    'inventory_quantity' => 0,
                    'weight'             => null,
                    'weight_unit'        => 'kg',
                    'requires_shipping'  => true,
                    'taxable'            => true,
                ],
                $matchedData,
                [
                    'title'    => $title,
                    'option1'  => $opt1,
                    'option2'  => $opt2,
                    'option3'  => $opt3,
                    'position' => $position++,
                ]
            );

            // Fall back to product-level defaults if variant price is blank
            if (blank($item['price'])) {
                $item['price'] = $get('price') ?? 0;
            }
            if (blank($item['compare_at_price'])) {
                $item['compare_at_price'] = $get('compare_at_price') ?: null;
            }

            if ($matchedKey !== null) {
                $matchedItems[$matchedKey] = $item;
            } else {
                $newItems[] = $item;
            }
        }

        // Assign new items with keys that don't collide with matched item keys.
        $existingIntKeys = array_filter(array_keys($matchedItems), 'is_int');
        $nextKey         = $existingIntKeys ? max($existingIntKeys) + 1 : 0;

        $newState = $matchedItems;
        foreach ($newItems as $item) {
            $newState[$nextKey++] = $item;
        }

        $set('variants', $newState);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('featured_image_url')
                    ->label('')
                    ->size(50)
                    ->getStateUsing(fn ($record) => $record->featuredImage
                        ? url('storage/' . $record->featuredImage->path)
                        : null
                    ),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('handle')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'draft'    => 'gray',
                        'archived' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('vendor')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('product_type')
                    ->label('Type')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('inventory_quantity')
                    ->label('Stok')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state, $record) => $record->track_stock ? $state : '—')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'active'   => 'Active',
                        'archived' => 'Archived',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('featuredImage');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
