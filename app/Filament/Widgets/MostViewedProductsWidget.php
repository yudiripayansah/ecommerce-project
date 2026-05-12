<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MostViewedProductsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Produk Paling Banyak Dilihat';

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
                    ->selectRaw('COUNT(pv.id) as views_count')
                    ->selectRaw('COALESCE(SUM(oi.quantity), 0) as total_qty')
                    ->leftJoin('product_views as pv', 'products.id', '=', 'pv.product_id')
                    ->leftJoin('order_items as oi', function ($join) {
                        $join->on('products.id', '=', 'oi.product_id');
                    })
                    ->leftJoin('orders as o', function ($join) {
                        $join->on('o.id', '=', 'oi.order_id')
                            ->whereNotIn('o.status', ['cancelled']);
                    })
                    ->groupBy('products.id', 'products.title', 'products.status', 'products.price', 'products.handle')
                    ->havingRaw('COUNT(pv.id) > 0')
                    ->orderByRaw('COUNT(pv.id) DESC')
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

                TextColumn::make('views_count')
                    ->label('Jumlah Dilihat')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_qty')
                    ->label('Terjual (Unit)')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([5, 10, 25]);
    }
}
