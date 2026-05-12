<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\StoreFile;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pengaturan Toko';

    protected static ?string $title = 'Pengaturan Toko';

    protected string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $logoUrl    = Setting::get('store_logo') ?: null;
        $faviconUrl = Setting::get('store_favicon') ?: null;

        $this->form->fill([
            'store_name'  => Setting::get('store_name', config('app.name', '')),
            'store_email' => Setting::get('store_email', ''),
            'store_phone' => Setting::get('store_phone', ''),

            'store_logo_url'    => $logoUrl,
            '_logo_preview'     => $logoUrl ? static::makeAssetPreviewHtml($logoUrl, 160) : null,
            'store_favicon_url' => $faviconUrl,
            '_favicon_preview'  => $faviconUrl ? static::makeAssetPreviewHtml($faviconUrl, 80) : null,

            'bank_accounts' => json_decode(Setting::get('bank_accounts', '[]'), true) ?: [],

            // Shipping
            'rajaongkir_origin_province_id' => Setting::get('rajaongkir_origin_province_id', ''),
            'rajaongkir_origin_city_id'     => Setting::get('rajaongkir_origin_city_id', ''),

            // Midtrans
            'midtrans_client_key'    => Setting::get('midtrans_client_key', ''),
            'midtrans_server_key'    => Setting::get('midtrans_server_key', ''),
            'midtrans_is_production' => filter_var(Setting::get('midtrans_is_production', false), FILTER_VALIDATE_BOOLEAN),

            // WhatsApp
            'fonnte_token'          => Setting::get('fonnte_token', ''),
            'fonnte_admin_whatsapp' => Setting::get('fonnte_admin_whatsapp', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $provinces = collect((new RajaOngkirService)->getProvinces())
            ->sortBy('province')
            ->pluck('province', 'province_id')
            ->toArray();

        return $schema
            ->statePath('data')
            ->components([

                // ── Informasi Toko ─────────────────────────────────────────
                Section::make('Informasi Toko')
                    ->columns(3)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('store_email')
                            ->label('Email Kontak')
                            ->email()
                            ->maxLength(150),
                        TextInput::make('store_phone')
                            ->label('Telepon Kontak')
                            ->tel()
                            ->maxLength(20),
                    ]),

                // ── Logo & Favicon ─────────────────────────────────────────
                Section::make('Logo & Favicon')
                    ->description('Logo ditampilkan di header toko. Favicon muncul di tab browser.')
                    ->columns(2)
                    ->schema([

                        // ── Logo ──────────────────────────────────────────
                        Section::make('Logo Toko')
                            ->compact()
                            ->headerActions([
                                Action::make('select_logo')
                                    ->label(fn (Get $get): string => $get('store_logo_url') ? 'Ganti Logo' : 'Pilih Logo')
                                    ->icon('heroicon-o-photo')
                                    ->modalHeading('Pilih Logo Toko')
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
                                                            ->directory(fn () => tenant_storage_prefix() . 'store-assets')
                                                            ->visibility('public')
                                                            ->image()
                                                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                                                            ->storeFileNamesIn('upload_original_name')
                                                            ->maxSize(2_048)
                                                            ->imagePreviewHeight('200')
                                                            ->helperText('PNG, SVG, atau WebP · Maks 2 MB'),
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
                                                                           . '<span style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;">' . e($f->filename) . '</span>'
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
                                        $url = null;

                                        if (! empty($data['upload_file'])) {
                                            $path = $data['upload_file'];
                                            $file = StoreFile::firstOrCreate(
                                                ['path' => $path, 'disk' => 'public'],
                                                static::buildAssetFileAttributes(
                                                    $path,
                                                    $data['upload_original_name'][$path] ?? basename($path)
                                                )
                                            );
                                            $url = $file->url;
                                        } elseif (! empty($data['file_id'])) {
                                            $id   = is_array($data['file_id']) ? reset($data['file_id']) : $data['file_id'];
                                            $file = StoreFile::find($id);
                                            if ($file) {
                                                $url = $file->url;
                                            }
                                        }

                                        if ($url) {
                                            $set('store_logo_url', $url);
                                            $set('_logo_preview', static::makeAssetPreviewHtml($url, 160));
                                        }
                                    }),

                                Action::make('remove_logo')
                                    ->label('Hapus')
                                    ->color('danger')
                                    ->icon('heroicon-o-trash')
                                    ->visible(fn (Get $get): bool => (bool) $get('store_logo_url'))
                                    ->requiresConfirmation()
                                    ->action(function (Set $set): void {
                                        $set('store_logo_url', null);
                                        $set('_logo_preview', null);
                                    }),
                            ])
                            ->schema([
                                Hidden::make('store_logo_url'),

                                ViewField::make('_logo_preview')
                                    ->view('filament.components.media-preview')
                                    ->visible(fn (Get $get): bool => (bool) $get('store_logo_url')),
                            ]),

                        // ── Favicon ───────────────────────────────────────
                        Section::make('Favicon')
                            ->compact()
                            ->headerActions([
                                Action::make('select_favicon')
                                    ->label(fn (Get $get): string => $get('store_favicon_url') ? 'Ganti Favicon' : 'Pilih Favicon')
                                    ->icon('heroicon-o-photo')
                                    ->modalHeading('Pilih Favicon')
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
                                                            ->directory(fn () => tenant_storage_prefix() . 'store-assets')
                                                            ->visibility('public')
                                                            ->image()
                                                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'])
                                                            ->storeFileNamesIn('upload_original_name')
                                                            ->maxSize(512)
                                                            ->imagePreviewHeight('128')
                                                            ->helperText('PNG atau ICO, 32×32 atau 64×64 px · Maks 512 KB'),
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
                                                                           . '<span style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;">' . e($f->filename) . '</span>'
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
                                        $url = null;

                                        if (! empty($data['upload_file'])) {
                                            $path = $data['upload_file'];
                                            $file = StoreFile::firstOrCreate(
                                                ['path' => $path, 'disk' => 'public'],
                                                static::buildAssetFileAttributes(
                                                    $path,
                                                    $data['upload_original_name'][$path] ?? basename($path)
                                                )
                                            );
                                            $url = $file->url;
                                        } elseif (! empty($data['file_id'])) {
                                            $id   = is_array($data['file_id']) ? reset($data['file_id']) : $data['file_id'];
                                            $file = StoreFile::find($id);
                                            if ($file) {
                                                $url = $file->url;
                                            }
                                        }

                                        if ($url) {
                                            $set('store_favicon_url', $url);
                                            $set('_favicon_preview', static::makeAssetPreviewHtml($url, 80));
                                        }
                                    }),

                                Action::make('remove_favicon')
                                    ->label('Hapus')
                                    ->color('danger')
                                    ->icon('heroicon-o-trash')
                                    ->visible(fn (Get $get): bool => (bool) $get('store_favicon_url'))
                                    ->requiresConfirmation()
                                    ->action(function (Set $set): void {
                                        $set('store_favicon_url', null);
                                        $set('_favicon_preview', null);
                                    }),
                            ])
                            ->schema([
                                Hidden::make('store_favicon_url'),

                                ViewField::make('_favicon_preview')
                                    ->view('filament.components.media-preview')
                                    ->visible(fn (Get $get): bool => (bool) $get('store_favicon_url')),
                            ]),
                    ]),

                // ── Rekening Bank ──────────────────────────────────────────
                Section::make('Rekening Bank')
                    ->description('Ditampilkan ke pelanggan yang memilih Transfer Bank saat checkout.')
                    ->schema([
                        Repeater::make('bank_accounts')
                            ->label(false)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->placeholder('BCA, Mandiri, BNI…')
                                    ->maxLength(50),
                                TextInput::make('account_number')
                                    ->label('Nomor Rekening')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('account_holder')
                                    ->label('Nama Pemilik Rekening')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->columns(3)
                            ->addActionLabel('Tambah Rekening')
                            ->reorderable(false)
                            ->defaultItems(0),
                    ]),

                // ── Pengiriman (RajaOngkir) ────────────────────────────────
                Section::make('Pengiriman')
                    ->description('Kota asal pengiriman digunakan untuk menghitung ongkos kirim via RajaOngkir.')
                    ->columns(2)
                    ->schema([
                        Select::make('rajaongkir_origin_province_id')
                            ->label('Provinsi Asal')
                            ->options($provinces)
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('rajaongkir_origin_city_id', null)),

                        Select::make('rajaongkir_origin_city_id')
                            ->label('Kota / Kabupaten Asal')
                            ->options(function (Get $get) {
                                $provinceId = $get('rajaongkir_origin_province_id');
                                if (! $provinceId) {
                                    return [];
                                }
                                return collect((new RajaOngkirService)->getCities((int) $provinceId))
                                    ->sortBy('city_name')
                                    ->pluck('city_name', 'city_id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->live()
                            ->helperText('Pilih provinsi dulu agar daftar kota muncul.'),
                    ]),

                // ── Midtrans ───────────────────────────────────────────────
                Section::make('Midtrans (Pembayaran Online)')
                    ->description('Daftarkan akun di midtrans.com lalu masukkan kunci di sini.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('midtrans_client_key')
                            ->label('Client Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('SB-Mid-client-xxxx / Mid-client-xxxx'),

                        TextInput::make('midtrans_server_key')
                            ->label('Server Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->placeholder('SB-Mid-server-xxxx / Mid-server-xxxx'),

                        Toggle::make('midtrans_is_production')
                            ->label('Mode Production')
                            ->helperText('Matikan untuk menggunakan Sandbox (testing). Nyalakan saat toko sudah live.')
                            ->columnSpanFull(),
                    ]),

                // ── WhatsApp (Fonnte) ──────────────────────────────────────
                Section::make('Notifikasi WhatsApp')
                    ->description('Daftarkan nomor WhatsApp di fonnte.com dan masukkan token di sini.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('fonnte_token')
                            ->label('Fonnte Token')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Token dari dashboard Fonnte (satu token per perangkat WA).'),

                        TextInput::make('fonnte_admin_whatsapp')
                            ->label('Nomor WA Admin Toko')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('628xxxxxxxxxx')
                            ->helperText('Menerima notifikasi setiap ada pesanan baru. Format: 628xxx.'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany([
            'store_name'    => $data['store_name'] ?? '',
            'store_email'   => $data['store_email'] ?? '',
            'store_phone'   => $data['store_phone'] ?? '',
            'store_logo'    => $data['store_logo_url'] ?? '',
            'store_favicon' => $data['store_favicon_url'] ?? '',
            'bank_accounts' => json_encode(array_values($data['bank_accounts'] ?? [])),

            'rajaongkir_origin_province_id' => $data['rajaongkir_origin_province_id'] ?? '',
            'rajaongkir_origin_city_id'     => $data['rajaongkir_origin_city_id'] ?? '',

            'midtrans_client_key'    => $data['midtrans_client_key'] ?? '',
            'midtrans_server_key'    => $data['midtrans_server_key'] ?? '',
            'midtrans_is_production' => $data['midtrans_is_production'] ? '1' : '0',

            'fonnte_token'          => $data['fonnte_token'] ?? '',
            'fonnte_admin_whatsapp' => $data['fonnte_admin_whatsapp'] ?? '',
        ]);

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    private static function buildAssetFileAttributes(string $path, ?string $originalName = null): array
    {
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

    private static function makeAssetPreviewHtml(string $url, int $height = 160): string
    {
        $src = e(parse_url($url, PHP_URL_PATH) ?? $url);

        return '<img src="' . $src . '" alt="preview" '
             . 'style="width:100%;height:' . $height . 'px;object-fit:contain;display:block;" loading="lazy" />';
    }
}
