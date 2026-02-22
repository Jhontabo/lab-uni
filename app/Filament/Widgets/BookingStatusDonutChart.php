<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BookingStatusDonutChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    protected static ?string $heading = 'Reservas por estado';

    protected static ?string $maxHeight = '300px';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        return cache()->remember('booking-status-chart', 300, function () {
            $data = Booking::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->orderBy('total', 'desc')
                ->get();

            $totalBookings = $data->sum('total');

            return [
                'labels' => $data->pluck('status')->map(fn ($status) => strtoupper($status))->toArray(),
                'datasets' => [
                    [
                        'label' => 'Total Bookings',
                        'data' => $data->pluck('total')->toArray(),
                        'backgroundColor' => $this->generateColors($data->count()),
                        'borderColor' => '#ffffff',
                        'borderWidth' => 1,
                    ],
                ],
            ];
        });
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function generateColors(int $count): array
    {
        $statusColors = [
            'approved' => '#10b981',   // Green
            'rejected' => '#ef4444',   // Red
            'pending' => '#f59e0b',    // Yellow
        ];

        $baseColors = [
            '#8b5cf6', // Purple
            '#06b6d4', // Cyan
            '#d946ef', // Pink
            '#84cc16', // Lime
        ];

        $colors = [];
        $statuses = Booking::groupBy('status')->pluck('status')->toArray();

        foreach ($statuses as $i => $status) {
            $colors[] = $statusColors[strtolower($status)] ?? $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }

    public function getDescription(): ?string
    {
        return 'Percentaje distribucion de reservas por estado';
    }
}
