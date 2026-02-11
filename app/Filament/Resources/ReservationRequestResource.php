<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationRequestResource\Pages;
use App\Models\Booking;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;

class ReservationRequestResource extends Resource
{
    protected static ?string $model = Booking::class;

    // protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Solicitud de reserva';

    protected static ?string $navigationGroup = 'Gestion de Reservas';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Solicitud';

    protected static ?string $pluralLabel = 'Solicitudes de reserva';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('ADMIN') || $user->hasRole('LABORATORISTA');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::getModel()::where('status', 'pending')->count() > 3 ? 'warning' : 'success';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label('Laboratorio')
                    ->description(fn ($record) => $record->laboratory?->location ?? 'Sin ubicación')
                    ->searchable()
                    ->icon('heroicon-o-building-office'),

                TextColumn::make('user.name')
                    ->label('Aplicante')
                    ->formatStateUsing(fn ($record) => "{$record->user->name} {$record->user->last_name}")
                    ->description(fn ($record) => $record->user->email ?? 'Sin correo')
                    ->icon('heroicon-o-user'),

                TextColumn::make('interval')
                    ->label('Intervalo')
                    ->getStateUsing(fn ($record) => $record->schedule && $record->schedule->start_at && $record->schedule->end_at
                        ? $record->schedule->start_at->format('d M Y, H:i').' - '.$record->schedule->end_at->format('H:i')
                        : 'No asignado')
                    ->description(fn ($record) => $record->schedule?->description ?? 'Sin descripción')
                    ->icon('heroicon-o-clock'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => ucfirst($state),
                    })
                    ->badge()
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
                    }),

                TextColumn::make('created_at')
                    ->label('Fecha de solicitud')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
            ])

            ->actions([
                Action::make('Aprobar')
                    ->action(function (Booking $record) {
                        $record->status = 'approved';
                        $record->save();

                        Notification::make()
                            ->title('¡Reserva Aprobada! 🎉')
                            ->body("Tu reserva para el laboratorio {$record->laboratory->name} ha sido aprobada. Horario: {$record->start_at->format('d/m/Y H:i')} - {$record->end_at->format('d/m/Y H:i')}")
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->sendToDatabase($record->user);

                        Notification::make()
                            ->success()
                            ->title('Reserva aprobada')
                            ->body("La solicitud de {$record->user->name} {$record->user->last_name} ha sido aprobada.")
                            ->send();
                    })
                    ->visible(fn (Booking $record) => $record->status === 'pending')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->modalHeading('Aprobar solicitud')
                    ->modalDescription('¿Está seguro de aprobar esta solicitud de reserva?')
                    ->requiresConfirmation(),

                Action::make('Rechazar')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->placeholder('Indique la razón del rechazo')
                            ->maxLength(503),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->status = 'rejected';
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();

                        Notification::make()
                            ->title('Reserva Rechazada ❌')
                            ->body("Tu reserva para el laboratorio {$record->laboratory->name} ha sido rechazada. Motivo: {$data['rejection_reason']}")
                            ->danger()
                            ->icon('heroicon-o-x-circle')
                            ->sendToDatabase($record->user);

                        Notification::make()
                            ->danger()
                            ->title('Reserva rechazada')
                            ->body("Solicitud de {$record->user->name} {$record->user->last_name} rechazada. Motivo: {$data['rejection_reason']}")
                            ->send();
                    })
                    ->visible(fn (Booking $record) => $record->status === 'pending')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->modalHeading('Rechazar solicitud')
                    ->modalDescription('Por favor indique el motivo del rechazo.'),

                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->label('Ver detalles'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('No hay solicitudes de reserva')
            ->emptyStateDescription('Aquí aparecerán las solicitudes enviadas por los usuarios.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationRequests::route('/'),
            'view' => Pages\ViewReservationRequest::route('/{record}'),
        ];
    }
}
