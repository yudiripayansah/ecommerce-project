<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected string $color = 'info';

    protected ?string $heading = 'Status Pesanan';

    protected ?string $description = 'Distribusi status semua pesanan';

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $statuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusLabels = [
            'pending'    => 'Pending',
            'processing' => 'Diproses',
            'shipped'    => 'Dikirim',
            'delivered'  => 'Terkirim',
            'cancelled'  => 'Dibatalkan',
        ];

        $statusColors = [
            'pending'    => '#f59e0b',
            'processing' => '#3b82f6',
            'shipped'    => '#8b5cf6',
            'delivered'  => '#10b981',
            'cancelled'  => '#ef4444',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusLabels as $key => $label) {
            $count = $statuses->get($key, 0);
            if ($count > 0) {
                $labels[] = $label;
                $data[] = $count;
                $colors[] = $statusColors[$key];
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((context.parsed / total) * 100).toFixed(1);
                            return ' ' + context.label + ': ' + context.parsed + ' (' + pct + '%)';
                        }",
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
