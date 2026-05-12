<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Customers';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Customer Information')
                ->columns(3)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                    \Filament\Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->counts('orders')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                TextColumn::make('total_spent')
                    ->label('Total spent')
                    ->getStateUsing(fn ($record) => 'Rp ' . number_format($record->orders()->sum('total'), 0, ',', '.'))
                    ->sortable(query: fn ($query, $direction) => $query->withSum('orders', 'total')->orderBy('orders_sum_total', $direction)),

                TextColumn::make('created_at')
                    ->label('Customer since')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
