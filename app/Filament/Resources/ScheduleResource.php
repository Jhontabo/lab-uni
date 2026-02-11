<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Resources\Resource;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    // protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Gestion de Reservas';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Crear Horarios';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Solo ADMIN y COORDINADOR pueden acceder
        return $user->hasRole('ADMIN') || $user->hasRole('COORDINADOR') || $user->hasRole('LABORATORISTA');
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ScheduleCalendar::route('/'),
        ];
    }
}
