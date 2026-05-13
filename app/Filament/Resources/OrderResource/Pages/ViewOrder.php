<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Jobs\SendOrderStatusNotificationJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    private function statusOptions(): array
    {
        return [
            'pending'    => 'Menunggu Pembayaran',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'delivered'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inputTracking')
                ->label('Input No. Resi')
                ->icon('heroicon-o-truck')
                ->color('gray')
                ->schema([
                    TextInput::make('tracking_number')
                        ->label('Nomor Resi')
                        ->default(fn () => $this->record->tracking_number)
                        ->placeholder('Contoh: JNE123456789')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['tracking_number' => $data['tracking_number']]);
                    $this->refreshFormData(['tracking_number']);
                }),

            Action::make('updateStatus')
                ->label('Update Status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->schema([
                    Select::make('status')
                        ->label('Status baru')
                        ->default(fn () => $this->record->status)
                        ->options($this->statusOptions())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $oldStatus = $this->record->status;
                    $newStatus = $data['status'];

                    $this->record->update(['status' => $newStatus]);

                    SendOrderStatusNotificationJob::dispatch(
                        $this->record->id,
                        $oldStatus,
                        tenant()->getTenantKey()
                    );

                    $this->refreshFormData(['status']);
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([

                // ── Ringkasan Pesanan ─────────────────────────────────────
                Section::make('Pesanan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('No. Pesanan')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label('Tanggal Pesanan')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('status')
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
                            ->formatStateUsing(fn ($state) => $this->statusOptions()[$state] ?? $state),

                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'midtrans'      => 'info',
                                'bank_transfer' => 'warning',
                                'cod'           => 'gray',
                                default         => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'midtrans'      => 'Bayar Online (Midtrans)',
                                'bank_transfer' => 'Transfer Bank',
                                'cod'           => 'Bayar di Tempat (COD)',
                                default         => $state,
                            }),
                    ]),

                // ── Rincian Harga ─────────────────────────────────────────
                Section::make('Rincian Harga')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->formatStateUsing(fn ($state) => rupiah($state)),

                        TextEntry::make('shipping_cost')
                            ->label('Ongkos Kirim')
                            ->formatStateUsing(fn ($state) => $state > 0 ? rupiah($state) : 'Gratis'),

                        TextEntry::make('total')
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => rupiah($state))
                            ->weight('bold'),
                    ]),

                // ── Data Pelanggan ────────────────────────────────────────
                Section::make('Data Pelanggan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer_name')->label('Nama'),
                        TextEntry::make('customer_email')->label('Email')->copyable(),
                        TextEntry::make('customer_phone')->label('Telepon')->copyable(),
                    ]),

                // ── Pengiriman ────────────────────────────────────────────
                Section::make('Pengiriman')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('shipping_address')
                            ->label('Alamat Lengkap')
                            ->columnSpan(4),

                        TextEntry::make('shipping_city')->label('Kota'),
                        TextEntry::make('shipping_province')->label('Provinsi'),
                        TextEntry::make('shipping_postal_code')->label('Kode Pos'),

                        TextEntry::make('tracking_number')
                            ->label('No. Resi')
                            ->placeholder('Belum diisi')
                            ->copyable()
                            ->badge()
                            ->color('success')
                            ->columnSpan(4),
                    ]),

                // ── Midtrans ──────────────────────────────────────────────
                Section::make('Info Midtrans')
                    ->columns(2)
                    ->hidden(fn ($record) => $record->payment_method !== 'midtrans')
                    ->schema([
                        TextEntry::make('midtrans_transaction_id')
                            ->label('Transaction ID')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('midtrans_payment_type')
                            ->label('Metode Bayar')
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state) => $state
                                ? str_replace('_', ' ', ucwords($state, '_'))
                                : '—'),
                    ]),

                // ── Items ─────────────────────────────────────────────────
                Section::make('Produk yang Dipesan')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label(false)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Produk')
                                    ->getStateUsing(fn ($record) =>
                                        $record->title . ($record->variant_title ? ' — ' . $record->variant_title : '')
                                    ),

                                TextEntry::make('price')
                                    ->label('Harga Satuan')
                                    ->formatStateUsing(fn ($state) => rupiah($state)),

                                TextEntry::make('quantity')->label('Qty'),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->getStateUsing(fn ($record) => rupiah($record->price * $record->quantity)),
                            ])
                            ->columns(4),
                    ]),

                // ── Catatan ───────────────────────────────────────────────
                Section::make('Catatan Pesanan')
                    ->schema([
                        TextEntry::make('notes')->label(false)->placeholder('—'),
                    ])
                    ->visible(fn ($record) => filled($record->notes)),

                // ── Bukti Transfer ────────────────────────────────────────
                Section::make('Bukti Transfer')
                    ->hidden(fn ($record) => $record->payment_method !== 'bank_transfer')
                    ->schema([
                        ImageEntry::make('payment_proof')
                            ->label(false)
                            ->disk('public')
                            ->placeholder('Belum ada bukti transfer')
                            ->extraImgAttributes(['class' => 'rounded-lg object-contain', 'style' => 'max-height:300px;']),
                    ]),

            ]);
    }
}
