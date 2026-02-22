<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationRequestResource\Pages;
use App\Models\Booking;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ReservationRequestResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Solicitud de reserva';

    protected static ?string $navigationGroup = 'Gestion de Reservas';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Solicitud';

    protected static ?string $pluralLabel = 'Solicitudes de reserva';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('ADMIN') || $user->hasRole('LABORATORISTA'));
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
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
                    ->label('Solicitante')
                    ->formatStateUsing(fn ($record) => "{$record->user->name} {$record->user->last_name}")
                    ->description(fn ($record) => $record->user->email ?? 'Sin correo')
                    ->searchable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('interval')
                    ->label('Horario')
                    ->getStateUsing(fn ($record) => $record->schedule && $record->schedule->start_at && $record->schedule->end_at
                        ? $record->schedule->start_at->format('d M Y, H:i').' - '.$record->schedule->end_at->format('H:i')
                        : 'No asignado')
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
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                    ]),
                SelectFilter::make('laboratory')
                    ->label('Laboratorio')
                    ->relationship('laboratory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Booking $record) => "Reserva #{$record->id}")
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn (Booking $record) => static::getDetailModalSchema($record)),

                ActionGroup::make([
                    Action::make('Aprobar')
                        ->action(function (Booking $record) {
                            $record->status = 'approved';
                            $record->save();

                            Notification::make()
                                ->title('Reserva Aprobada')
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
                                ->title('Reserva Rechazada')
                                ->body("Tu reserva para el laboratorio {$record->laboratory->name} ha sido rechazada. Motivo: {$data['rejection_reason']}")
                                ->danger()
                                ->icon('heroicon-o-x-circle')
                                ->sendToDatabase($record->user);

                            Notification::make()
                                ->danger()
                                ->title('Reserva rechazada')
                                ->body("Solicitud de {$record->user->name} {$record->user->last_name} rechazada.")
                                ->send();
                        })
                        ->visible(fn (Booking $record) => $record->status === 'pending')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->modalHeading('Rechazar solicitud')
                        ->modalDescription('Por favor indique el motivo del rechazo.'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->visible(fn (Booking $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('No hay solicitudes de reserva')
            ->emptyStateDescription('Aquí aparecerán las solicitudes enviadas por los usuarios.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    protected static function getDetailModalSchema(Booking $record): array
    {
        $productNames = [];
        if (! empty($record->products)) {
            $ids = is_array($record->products) ? $record->products : explode(',', $record->products);
            $productNames = Product::whereIn('id', $ids)->pluck('name')->toArray();
        }

        $statusBadge = match ($record->status) {
            'pending' => '<span style="color: #f59e0b; font-weight: 600;">Pendiente</span>',
            'approved' => '<span style="color: #10b981; font-weight: 600;">Aprobada</span>',
            'rejected' => '<span style="color: #ef4444; font-weight: 600;">Rechazada</span>',
            default => ucfirst($record->status),
        };

        $schema = [
            Section::make('Estado de la solicitud')
                ->icon('heroicon-o-information-circle')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('status_display')
                            ->label('Estado')
                            ->content(new HtmlString($statusBadge)),
                        Placeholder::make('created_display')
                            ->label('Fecha de solicitud')
                            ->content($record->created_at->format('d/m/Y H:i')),
                        Placeholder::make('updated_display')
                            ->label('Última actualización')
                            ->content($record->updated_at->format('d/m/Y H:i')),
                    ]),
                ]),

            Section::make('Solicitante')
                ->icon('heroicon-o-user')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('name_display')
                            ->label('Nombre')
                            ->content(trim($record->name.' '.$record->last_name)),
                        Placeholder::make('email_display')
                            ->label('Correo')
                            ->content($record->email),
                        Placeholder::make('applicants_display')
                            ->label('Otros solicitantes')
                            ->content($record->applicants ?: 'Ninguno'),
                    ]),
                ]),

            Section::make('Información del proyecto')
                ->icon('heroicon-o-academic-cap')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('project_type_display')
                            ->label('Tipo de proyecto')
                            ->content($record->project_type ?? 'No especificado'),
                        Placeholder::make('academic_program_display')
                            ->label('Programa académico')
                            ->content($record->academic_program ?? 'No especificado'),
                        Placeholder::make('semester_display')
                            ->label('Semestre')
                            ->content($record->semester ?? 'No especificado'),
                    ]),
                    Grid::make(2)->schema([
                        Placeholder::make('research_display')
                            ->label('Investigación')
                            ->content($record->research_name ?? 'No especificado'),
                        Placeholder::make('advisor_display')
                            ->label('Asesor')
                            ->content($record->advisor ?? 'No especificado'),
                    ]),
                ]),

            Section::make('Espacio y horario')
                ->icon('heroicon-o-calendar-days')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('lab_display')
                            ->label('Laboratorio')
                            ->content(($record->laboratory->name ?? 'No asignado').($record->laboratory?->location ? " ({$record->laboratory->location})" : '')),
                        Placeholder::make('schedule_display')
                            ->label('Horario')
                            ->content($record->schedule && $record->schedule->start_at
                                ? $record->schedule->start_at->format('d/m/Y H:i').' - '.$record->schedule->end_at->format('H:i')
                                : 'No asignado'),
                        Placeholder::make('duration_display')
                            ->label('Duración')
                            ->content($record->schedule && $record->schedule->start_at
                                ? $record->schedule->start_at->diffInHours($record->schedule->end_at).' horas'
                                : 'N/A'),
                    ]),
                ]),

            Section::make('Materiales y equipos')
                ->icon('heroicon-o-beaker')
                ->compact()
                ->schema([
                    Placeholder::make('products_display')
                        ->label('Productos solicitados')
                        ->content(! empty($productNames)
                            ? new HtmlString('<ul class="list-disc ml-4">'.implode('', array_map(fn ($n) => "<li>{$n}</li>", $productNames)).'</ul>')
                            : 'No se especificaron materiales.'),
                ]),
        ];

        if ($record->status === 'rejected' && $record->rejection_reason) {
            $schema[] = Section::make('Motivo del rechazo')
                ->icon('heroicon-o-x-circle')
                ->compact()
                ->schema([
                    Placeholder::make('rejection_display')
                        ->label('')
                        ->content(new HtmlString('<p class="p-3 rounded-lg bg-danger-50 dark:bg-danger-900/50 text-danger-700 dark:text-danger-300">'.$record->rejection_reason.'</p>')),
                ]);
        }

        return $schema;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationRequests::route('/'),
        ];
    }
}
