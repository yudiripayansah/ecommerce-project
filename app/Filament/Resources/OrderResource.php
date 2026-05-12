<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pesanan';

    public static function getNavigationBadge(): ?string
    {
        return (string) Order::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    private static function statusOptions(): array
    {
        return [
            'pending'    => 'Menunggu Pembayaran',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'delivered'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
        ];
    }

    private static function paymentOptions(): array
    {
        return [
            'cod'           => 'Bayar di Tempat (COD)',
            'bank_transfer' => 'Transfer Bank',
            'midtrans'      => 'Bayar Online (Midtrans)',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Pesanan')
                ->columns(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('order_number')
                        ->label('No. Pesanan')
                        ->disabled(),

                    Select::make('status')
                        ->label('Status')
                        ->options(self::statusOptions())
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->formatStateUsing(fn ($state) => self::paymentOptions()[$state] ?? $state)
                        ->disabled(),

                    \Filament\Forms\Components\TextInput::make('total')
                        ->label('Total')
                        ->prefix('Rp')
                        ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.'))
                        ->disabled(),
                ]),

            Section::make('Data Pelanggan')
                ->columns(3)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('customer_name')->label('Nama')->disabled(),
                    \Filament\Forms\Components\TextInput::make('customer_email')->label('Email')->disabled(),
                    \Filament\Forms\Components\TextInput::make('customer_phone')->label('Telepon')->disabled(),
                ]),

            Section::make('Alamat Pengiriman')
                ->columns(3)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('shipping_address')
                        ->label('Alamat')->columnSpanFull()->disabled(),
                    \Filament\Forms\Components\TextInput::make('shipping_city')->label('Kota')->disabled(),
                    \Filament\Forms\Components\TextInput::make('shipping_province')->label('Provinsi')->disabled(),
                    \Filament\Forms\Components\TextInput::make('shipping_postal_code')->label('Kode Pos')->disabled(),
                ]),

            Section::make('Catatan')
                ->schema([
                    Textarea::make('notes')->label(false)->rows(3)->disabled(),
                ])
                ->visible(fn ($record) => filled($record?->notes)),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->description(fn ($record) => $record->customer_phone),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => self::statusOptions()[$state] ?? $state),

                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'midtrans'      => 'info',
                        'bank_transfer' => 'warning',
                        'cod'           => 'gray',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'midtrans'      => 'Midtrans',
                        'bank_transfer' => 'Transfer Bank',
                        'cod'           => 'COD',
                        default         => $state,
                    }),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state) => rupiah($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('shipping_cost')
                    ->label('Ongkir')
                    ->formatStateUsing(fn ($state) => $state > 0 ? rupiah($state) : '—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => rupiah($state))
                    ->sortable(),

                TextColumn::make('shipping_city')
                    ->label('Kota Tujuan')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::statusOptions()),

                SelectFilter::make('payment_method')
                    ->label('Pembayaran')
                    ->options(self::paymentOptions()),
            ])
            ->actions([
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->form([
                        Select::make('status')
                            ->label('Status baru')
                            ->options(self::statusOptions())
                            ->required(),
                    ])
                    ->action(fn (Order $record, array $data) => $record->update(['status' => $data['status']])),

                \Filament\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
