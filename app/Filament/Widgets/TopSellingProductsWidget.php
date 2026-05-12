<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Produk Terlaris';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select([
                        'products.id',
                        'products.title',
                        'products.status',
                        'products.price',
                        'products.handle',
                    ])
                    ->selectRaw('COALESCE(SUM(oi.quantity), 0) as total_qty')
                    ->selectRaw('COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue')
                    ->leftJoin('order_items as oi', 'products.id', '=', 'oi.product_id')
                    ->leftJoin('orders as o', function ($join) {
                        $join->on('o.id', '=', 'oi.order_id')
                            ->whereNotIn('o.status', ['cancelled']);
                    })
                    ->groupBy('products.id', 'products.title', 'products.status', 'products.price', 'products.handle')
                    ->havingRaw('COALESCE(SUM(oi.quantity), 0) > 0')
                    ->orderByRaw('COALESCE(SUM(oi.quantity), 0) DESC')
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Produk')
                    ->searchable()
                    ->limit(40)
                    ->weight('semibold'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active'   => 'success',
                        'draft'    => 'gray',
                        'archived' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('total_qty')
                    ->label('Terjual (Unit)')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 25]);
    }
}
