<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([

                // ── Statistik ringkas ──────────────────────────────────────
                Section::make('Ringkasan')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('orders_count')
                            ->label('Total Pesanan')
                            ->getStateUsing(fn ($record) => $record->orders()->count() . ' pesanan')
                            ->weight('bold'),

                        TextEntry::make('total_spent')
                            ->label('Total Belanja')
                            ->getStateUsing(fn ($record) => rupiah($record->orders()->sum('total')))
                            ->weight('bold'),

                        TextEntry::make('last_order_at')
                            ->label('Pesanan Terakhir')
                            ->getStateUsing(fn ($record) => $record->orders()->latest()->value('created_at')
                                ? \Carbon\Carbon::parse($record->orders()->latest()->value('created_at'))->translatedFormat('d M Y')
                                : '—'),

                        TextEntry::make('created_at')
                            ->label('Bergabung Sejak')
                            ->date('d M Y'),
                    ]),

                // ── Informasi kontak ───────────────────────────────────────
                Section::make('Informasi Kontak')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Nama Lengkap'),
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('phone')->label('Telepon')->copyable()->placeholder('—'),
                    ]),

                // ── Alamat tersimpan ───────────────────────────────────────
                Section::make('Alamat Tersimpan')
                    ->hidden(fn ($record) => $record->addresses()->count() === 0)
                    ->schema([
                        RepeatableEntry::make('addresses')
                            ->label(false)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama')
                                    ->weight('bold')
                                    ->getStateUsing(fn ($record) =>
                                        $record->name . ($record->is_default ? ' ★' : '')
                                    ),
                                TextEntry::make('phone')->label('Telepon')->placeholder('—'),
                                TextEntry::make('address')->label('Alamat')->columnSpan(2),
                                TextEntry::make('city')->label('Kota'),
                                TextEntry::make('province')->label('Provinsi'),
                                TextEntry::make('postal_code')->label('Kode Pos'),
                            ])
                            ->columns(2)
                            ->extraAttributes(['style' => 'max-height:300px;overflow-y:auto;']),
                    ]),

                // ── Riwayat pesanan ────────────────────────────────────────
                Section::make('Riwayat Pesanan')
                    ->schema([
                        RepeatableEntry::make('orders')
                            ->label(false)
                            ->schema([
                                // Baris 1
                                TextEntry::make('order_number')
                                    ->label('No. Pesanan')
                                    ->weight('bold')
                                    ->copyable(),

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
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'pending'    => 'Menunggu',
                                        'processing' => 'Diproses',
                                        'shipped'    => 'Dikirim',
                                        'delivered'  => 'Selesai',
                                        'cancelled'  => 'Dibatalkan',
                                        default      => $state,
                                    }),

                                TextEntry::make('payment_method')
                                    ->label('Pembayaran')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'midtrans'      => 'Midtrans',
                                        'bank_transfer' => 'Transfer Bank',
                                        'cod'           => 'COD',
                                        default         => $state,
                                    }),

                                // Baris 2
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->formatStateUsing(fn ($state) => rupiah($state))
                                    ->weight('bold'),

                                TextEntry::make('shipping_city')
                                    ->label('Kota Tujuan')
                                    ->placeholder('—'),

                                TextEntry::make('created_at')
                                    ->label('Tanggal')
                                    ->date('d M Y'),
                            ])
                            ->columns(3)
                            ->extraAttributes(['style' => 'max-height:480px;overflow-y:auto;padding-right:4px;']),
                    ]),

            ]);
    }
}
