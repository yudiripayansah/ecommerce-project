<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected string $color = 'success';

    protected ?string $heading = 'Grafik Penjualan';

    protected ?string $description = 'Revenue dan jumlah pesanan 30 hari terakhir';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getFilters(): ?array
    {
        return [
            '7'  => '7 Hari Terakhir',
            '30' => '30 Hari Terakhir',
            '90' => '90 Hari Terakhir',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 30);
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $orders = Order::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $revenueData = [];
        $countData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->format('d M');
            $labels[] = $label;

            $row = $orders->get($date);
            $revenueData[] = $row ? (float) $row->revenue : 0;
            $countData[] = $row ? (int) $row->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $revenueData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => $countData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }",
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'top'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            if (context.dataset.yAxisID === 'y') {
                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return context.dataset.label + ': ' + context.parsed.y;
                        }",
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
