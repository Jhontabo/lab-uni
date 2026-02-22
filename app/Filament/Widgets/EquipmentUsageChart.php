<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EquipmentUsageChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Equipos Más Utilizados';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return cache()->remember('equipment-usage-chart', 600, function () {
            $startDate = now()->subMonth()->toDateTimeString();
            $endDate = now()->toDateTimeString();

            $usageData = Product::query()
                ->select([
                    'products.*',
                    DB::raw("(SELECT SUM(TIMESTAMPDIFF(HOUR, booking_product.start_at, booking_product.end_at))
                              FROM bookings
                              INNER JOIN booking_product ON bookings.id = booking_product.booking_id
                              WHERE products.id = booking_product.product_id
                              AND bookings.status = 'approved'
                              AND booking_product.start_at BETWEEN '{$startDate}' AND '{$endDate}') as total_hours"),
                ])
                ->orderByDesc('total_hours')
                ->limit(5)
                ->get();

            return [
                'labels' => $usageData->pluck('name'),
                'datasets' => [
                    [
                        'label' => 'Horas de Uso (último mes)',
                        'data' => $usageData->pluck('total_hours'),
                        'backgroundColor' => [
                            '#3b82f6',
                            '#ef4444',
                            '#10b981',
                            '#f59e0b',
                            '#6366f1',
                        ],
                    ],
                ],
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Horas de Uso',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Equipos',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, // Ocultar leyenda para ahorrar espacio
                ],
            ],
        ];
    }
}
