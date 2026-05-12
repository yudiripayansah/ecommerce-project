<?php

namespace App\Filament\Super\Resources;

use App\Filament\Super\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Toko';

    protected static ?string $modelLabel = 'Toko';

    protected static ?string $pluralModelLabel = 'Daftar Toko';

    private static function planOptions(): array
    {
        return [
            'free'    => 'Free',
            'starter' => 'Starter',
            'pro'     => 'Pro',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Toko')
                ->columns(2)
                ->schema([
                    TextInput::make('id')
                        ->label('Subdomain / ID')
                        ->placeholder('toko-abc')
                        ->helperText('Huruf kecil, angka, dan strip. Digunakan sebagai subdomain.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9\-]+$/')
                        ->disabledOn('edit'),

                    TextInput::make('name')
                        ->label('Nama Toko')
                        ->required(),

                    Select::make('plan')
                        ->label('Paket')
                        ->options(self::planOptions())
                        ->default('free')
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ]),

            Section::make('Domain')
                ->description('Domain utama toko ini. Contoh: toko-abc (akan menjadi toko-abc.ezstore.id)')
                ->schema([
                    TextInput::make('domains.0.domain')
                        ->label('Subdomain')
                        ->suffix('.' . env('CENTRAL_DOMAIN', 'localhost'))
                        ->placeholder('toko-abc')
                        ->helperText('Kosongkan jika subdomain sama dengan ID toko.')
                        ->dehydrated(false),
                ])
                ->visibleOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Subdomain')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable(),

                TextColumn::make('plan')
                    ->label('Paket')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pro'     => 'success',
                        'starter' => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => self::planOptions()[$state] ?? $state),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('domains.domain')
                    ->label('Domain')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('visit')
                    ->label('Buka Toko')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Tenant $record) => 'http://' . ($record->domains->first()?->domain ?? $record->id . '.' . env('CENTRAL_DOMAIN', 'localhost')))
                    ->openUrlInNewTab(),

                \Filament\Actions\EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
