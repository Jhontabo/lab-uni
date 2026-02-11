<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationHistorysResource\Pages;
use App\Models\Booking;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReservationHistorysResource extends Resource
{
    protected static ?string $model = Booking::class;

    // protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Mis Reservas';

    protected static ?string $navigationGroup = 'Gestion de Reservas';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralLabel = 'Mis Reservas';

    public static function getNavigationBadge(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        return static::getModel()::where('user_id', Auth::id())->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        if (! Auth::check()) {
            return 'gray';
        }

        $pendientes = static::getModel()::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->count();

        return $pendientes > 0 ? 'warning' : 'success';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user &&
            ! $user->hasRole('COORDINADOR');
    }

    public static function getEloquentQuery(): Builder
    {
        // Debug: Log current user ID
        if (Auth::check()) {
            \Log::info('ReservationHistorysResource: User ID '.Auth::id().' is accessing their reservations');
        } else {
            \Log::warning('ReservationHistorysResource: No authenticated user found');

            // Return empty result if no user is authenticated
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $query = parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->with(['schedule', 'laboratory'])
            ->latest();

        // Debug: Log the SQL query
        \Log::info('ReservationHistorysResource: SQL Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label('Laboratorio')
                    ->description(fn ($record) => $record->laboratory?->location ?? 'Sin ubicación')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office'),

                TextColumn::make('interval')
                    ->label('Horario')
                    ->getStateUsing(function ($record) {
                        if (! $record->schedule) {
                            return 'No asignado';
                        }
                        $start = $record->schedule->start_at->format('d/m/Y H:i');
                        $end = $record->schedule->end_at->format('H:i');

                        return "{$start} - {$end}";
                    })
                    ->description(fn ($record) => $record->schedule?->description ?? 'Sin descripción')
                    ->icon('heroicon-o-clock'),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente de aprobación',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icon(fn ($state) => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => null,
                    })
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ])
                    ->label('Estado de la reserva'),

                SelectFilter::make('laboratory')
                    ->relationship('laboratory', 'name')
                    ->label('Filtrar por laboratorio'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->label('Ver detalles'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Aún no hay reservas')
            ->emptyStateDescription('Tus reservas aparecerán aquí una vez que las crees.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationHistories::route('/'),
            'view' => Pages\ViewReservationHistory::route('/{record}'),
        ];
    }
}
