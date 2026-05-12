<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $totalRevenue = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $revenueThisMonth = Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->sum('total');
        $revenueLastMonth = Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total');

        $totalOrders = Order::count();
        $ordersThisMonth = Order::whereBetween('created_at', [$startOfMonth, $now])->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        $totalCustomers = Customer::count();
        $customersThisMonth = Customer::whereBetween('created_at', [$startOfMonth, $now])->count();
        $customersLastMonth = Customer::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        $avgOrderValue = Order::whereNotIn('status', ['cancelled'])->avg('total') ?? 0;

        $pendingOrders = Order::where('status', 'pending')->count();

        $revenueChart = $this->getRevenueChart();
        $ordersChart = $this->getOrdersChart();

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description($this->growthDescription($revenueThisMonth, $revenueLastMonth) . ' bulan ini')
                ->descriptionIcon(
                    $revenueThisMonth >= $revenueLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'
                )
                ->color($revenueThisMonth >= $revenueLastMonth ? 'success' : 'danger')
                ->chart($revenueChart)
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Pesanan', number_format($totalOrders, 0, ',', '.'))
                ->description($this->growthDescription($ordersThisMonth, $ordersLastMonth) . ' bulan ini')
                ->descriptionIcon(
                    $ordersThisMonth >= $ordersLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'
                )
                ->color($ordersThisMonth >= $ordersLastMonth ? 'success' : 'danger')
                ->chart($ordersChart)
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Pesanan Pending', $pendingOrders)
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Total Pelanggan', number_format($totalCustomers, 0, ',', '.'))
                ->description($this->growthDescription($customersThisMonth, $customersLastMonth) . ' bulan ini')
                ->descriptionIcon(
                    $customersThisMonth >= $customersLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'
                )
                ->color($customersThisMonth >= $customersLastMonth ? 'success' : 'danger')
                ->icon('heroicon-o-user-group'),

            Stat::make('Rata-rata Nilai Pesanan', 'Rp ' . number_format($avgOrderValue, 0, ',', '.'))
                ->description('Per transaksi (non-cancel)')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->icon('heroicon-o-receipt-percent'),

            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->description(
                    $revenueLastMonth > 0
                        ? number_format(abs((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100), 1) . '% vs bulan lalu'
                        : 'Bulan ini'
                )
                ->descriptionIcon($revenueThisMonth >= $revenueLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueThisMonth >= $revenueLastMonth ? 'success' : 'danger')
                ->icon('heroicon-o-chart-bar'),
        ];
    }

    private function growthDescription(float $current, float $last): string
    {
        if ($last == 0) {
            return $current > 0 ? '+' . number_format($current, 0, ',', '.') : '0';
        }

        $pct = (($current - $last) / $last) * 100;
        $sign = $pct >= 0 ? '+' : '';

        return $sign . number_format($pct, 1) . '%';
    }

    private function getRevenueChart(): array
    {
        return Order::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    private function getOrdersChart(): array
    {
        return Order::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }
}
