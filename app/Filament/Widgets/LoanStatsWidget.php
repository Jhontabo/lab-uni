<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LoanStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('LABORATORISTA')) {
            return [];
        }

        $pending = Loan::where('status', 'pending')->count();
        $approved = Loan::where('status', 'approved')->count();
        $overdue = Loan::where('status', 'approved')
            ->where('estimated_return_at', '<', now())
            ->count();
        $returned = Loan::where('status', 'returned')
            ->whereMonth('actual_return_at', now()->month)
            ->count();

        return [
            Stat::make('Pendientes de Aprobación', $pending)
                ->description('Préstamos esperando respuesta')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'success')
                ->chart([$pending, $approved, $overdue]),

            Stat::make('Préstamos Activos', $approved)
                ->description('Equipos en uso')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Vencidos', $overdue)
                ->description('Devoluciones pendientes')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'gray'),

            Stat::make('Devueltos este mes', $returned)
                ->description('Préstamos completados')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('success'),
        ];
    }
}
