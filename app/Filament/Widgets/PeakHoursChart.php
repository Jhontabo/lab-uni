<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PeakHoursChart extends ChartWidget
{
    protected static ?string $heading = 'Horas Pico de Demanda';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $cacheKey = 'peak-hours-'.now()->format('Y-m');

        return cache()->remember($cacheKey, 600, function () {
            $start = Carbon::now()->subMonth();
            $end = Carbon::now();

            $data = Booking::whereBetween('start_at', [$start, $end])
                ->where('status', 'approved')
                ->selectRaw('HOUR(start_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            return [
                'labels' => $data->map(fn ($item) => $item->hour.':00'),
                'datasets' => [
                    [
                        'label' => 'Reservas por hora',
                        'data' => $data->pluck('count'),
                        'backgroundColor' => '#3b82f6',
                        'borderColor' => '#1d4ed8',
                    ],
                ],
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
