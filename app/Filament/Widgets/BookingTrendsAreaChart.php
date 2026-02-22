<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BookingTrendsAreaChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    protected static ?string $heading = 'Tendecias de reservas Mensuales';

    protected static ?string $maxHeight = '350px';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $cacheKey = 'booking-trends-'.now()->format('Y-m');

        return cache()->remember($cacheKey, 300, function () {
            $query = Booking::query()
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])
                ->groupBy('date')
                ->orderBy('date');

            $data = $query->get();

            return [
                'labels' => $data->pluck('date')->map(fn ($date) => $this->formatDate($date))->toArray(),
                'datasets' => [
                    [
                        'label' => 'Reservas',
                        'data' => $data->pluck('total')->toArray(),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'borderColor' => '#3b82f6',
                        'borderWidth' => 2,
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function formatDate($date)
    {
        return \Carbon\Carbon::parse($date)->format('d M');
    }

    public function getDescription(): ?string
    {
        return 'Tendencia de reservas diarias para el mes actual';
    }
}
