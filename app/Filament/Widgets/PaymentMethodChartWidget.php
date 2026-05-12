<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class PaymentMethodChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected string $color = 'warning';

    protected ?string $heading = 'Metode Pembayaran';

    protected ?string $description = 'Perbandingan COD vs Transfer Bank';

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected function getFilters(): ?array
    {
        return [
            'all'   => 'Semua Waktu',
            '30'    => '30 Hari Terakhir',
            '7'     => '7 Hari Terakhir',
        ];
    }

    protected function getData(): array
    {
        $query = Order::selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method');

        if ($this->filter && $this->filter !== 'all') {
            $query->where('created_at', '>=', now()->subDays((int) $this->filter));
        }

        $results = $query->get()->keyBy('payment_method');

        $cod = $results->get('cod');
        $bank = $results->get('bank_transfer');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pesanan',
                    'data' => [
                        $cod ? $cod->count : 0,
                        $bank ? $bank->count : 0,
                    ],
                    'backgroundColor' => ['#f59e0b', '#3b82f6'],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => ['COD', 'Transfer Bank'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
