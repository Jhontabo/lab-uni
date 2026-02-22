<?php

namespace App\Filament\Widgets;

use App\Models\Laboratory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BookingByLaboratoryChart extends ChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    protected static ?string $heading = 'Reservas por laboratorio';

    protected static ?string $maxHeight = '300px';

    protected static ?int $sort = 3;

    public ?int $laboratoryId = null;

    protected function getData(): array
    {
        $cacheKey = 'booking-by-lab-'.($this->laboratoryId ?? 'all');

        return cache()->remember($cacheKey, 300, function () {
            // Get the laboratory from session if available
            $this->laboratoryId = session()->get('lab');

            // Query to count bookings per laboratory
            $query = Laboratory::query()
                ->leftJoin('bookings', 'laboratories.id', '=', 'bookings.laboratory_id')
                ->select(
                    'laboratories.name',
                    DB::raw('COUNT(bookings.id) as total_bookings')
                )
                ->groupBy('laboratories.id', 'laboratories.name')
                ->orderBy('total_bookings', 'desc');

            // Filter by laboratory if specified
            if ($this->laboratoryId) {
                $query->where('laboratories.id', $this->laboratoryId);
            }

            $data = $query->get();

            return [
                'labels' => $data->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Total reservas',
                        'data' => $data->pluck('total_bookings')->toArray(),
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
        return 'bar';
    }

    protected function generateColors(int $count): array
    {
        $colors = [];
        $baseColors = [
            '#4f46e5',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#06b6d4',
            '#d946ef',
            '#84cc16',
        ];

        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }

        return $colors;
    }

    public function getDescription(): ?string
    {
        return 'Distribucion de reservas por laboratory';
    }
}
