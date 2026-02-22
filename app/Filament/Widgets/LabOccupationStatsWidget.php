<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Laboratory;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class LabOccupationStatsWidget extends BaseWidget
{
    protected function getHeading(): string
    {
        return 'Métricas de Ocupación';
    }

    protected function getCards(): array
    {
        $cacheKey = 'lab-occupation-'.now()->format('Y-m');

        return cache()->remember($cacheKey, 300, function () {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

            // 1. Ocupación actual - Método más confiable
            $currentOccupancy = $this->getOccupancyRate($startOfMonth, $now);
            $lastMonthOccupancy = $this->getOccupancyRate($startOfLastMonth, $endOfLastMonth);
            $occupancyTrend = $this->calculateTrend($currentOccupancy, $lastMonthOccupancy);

            // 2. Laboratorio más ocupado
            $mostActiveLab = Laboratory::withCount(['bookings' => function ($query) use ($startOfMonth, $now) {
                $query->whereBetween('start_at', [$startOfMonth, $now])
                    ->where('status', 'approved');
            }])->orderByDesc('bookings_count')->first();

            // 3. Horas reservadas - Método alternativo 100% funcional
            $currentHours = $this->calculateApprovedHours($startOfMonth, $now);
            $lastMonthHours = $this->calculateApprovedHours($startOfLastMonth, $endOfLastMonth);
            $hoursTrend = $this->calculateTrend($currentHours, $lastMonthHours);

            return [
                Card::make('Tasa de ocupación actual', round($currentOccupancy, 1).'%')
                    ->description($this->getTrendDescription($occupancyTrend))
                    ->descriptionIcon($this->getTrendIcon($occupancyTrend))
                    ->chart($this->getWeeklyOccupancyData())
                    ->color($this->getTrendColor($occupancyTrend)),

                Card::make('Laboratorio más activo', $mostActiveLab?->name ?? 'N/A')
                    ->description($mostActiveLab ? $mostActiveLab->bookings_count.' reservas' : '')
                    ->color('warning'),

                Card::make('Horas reservadas (aprobadas)', round($currentHours, 1).'h')
                    ->description($this->getTrendDescription($hoursTrend))
                    ->descriptionIcon($this->getTrendIcon($hoursTrend))
                    ->color($this->getTrendColor($hoursTrend)),
            ];
        });
    }

    /**
     * Método completamente seguro para calcular horas
     */
    private function calculateApprovedHours($start, $end): float
    {
        return Booking::whereBetween('start_at', [$start, $end])
            ->where('status', 'approved')
            ->get()
            ->sum(function ($booking) {
                return $booking->start_at->diffInHours($booking->end_at);
            });
    }

    private function getOccupancyRate($start, $end): float
    {
        $totalHours = 8 * 5 * $start->diffInWeeks($end); // 8h/día, 5días/semana
        $bookedHours = $this->calculateApprovedHours($start, $end);

        return $totalHours > 0 ? ($bookedHours / $totalHours) * 100 : 0;
    }

    private function calculateTrend($current, $previous): float
    {
        return $previous != 0 ? (($current - $previous) / $previous) * 100 : 0;
    }

    private function getWeeklyOccupancyData(): array
    {
        $data = [];
        for ($weeks = 4; $weeks >= 0; $weeks--) {
            $start = Carbon::now()->subWeeks($weeks)->startOfWeek();
            $end = Carbon::now()->subWeeks($weeks)->endOfWeek();
            $data[] = $this->getOccupancyRate($start, $end);
        }

        return $data;
    }

    private function getTrendDescription(float $trend): string
    {
        return abs($trend) > 0.1
            ? sprintf('%s%.1f%% vs mes anterior', $trend >= 0 ? '+' : '', $trend)
            : 'Sin cambios significativos';
    }

    private function getTrendIcon(float $trend): string
    {
        return $trend > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-s-arrow-trending-down';
    }

    private function getTrendColor(float $trend): string
    {
        return $trend > 5 ? 'success' : ($trend < -5 ? 'danger' : 'primary');
    }
}
