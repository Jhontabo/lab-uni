<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationHistorysResource\Pages;
use App\Models\Booking;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ReservationHistorysResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

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

        $count = static::getModel()::where('user_id', Auth::id())->count();

        return $count > 0 ? (string) $count : null;
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

        return $user && ! $user->hasRole('COORDINADOR');
    }

    public static function getEloquentQuery(): Builder
    {
        if (! Auth::check()) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->where('user_id', Auth::id())
            ->with(['schedule', 'laboratory'])
            ->latest();
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

                TextColumn::make('research_name')
                    ->label('Investigación')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->research_name),

                TextColumn::make('interval')
                    ->label('Horario')
                    ->getStateUsing(function ($record) {
                        if (! $record->schedule) {
                            return 'No asignado';
                        }

                        return $record->schedule->start_at->format('d/m/Y H:i').' - '.$record->schedule->end_at->format('H:i');
                    })
                    ->icon('heroicon-o-clock'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
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
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Solicitado')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ])
                    ->label('Estado'),

                SelectFilter::make('laboratory')
                    ->relationship('laboratory', 'name')
                    ->label('Laboratorio')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Action::make('ver')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Booking $record) => "Reserva #{$record->id}")
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn (Booking $record) => static::getDetailModalSchema($record)),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Aún no tienes reservas')
            ->emptyStateDescription('Cuando realices una reserva, aparecerá aquí para que puedas darle seguimiento.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    protected static function getDetailModalSchema(Booking $record): array
    {
        $productNames = [];
        if (! empty($record->products)) {
            $raw = $record->products;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $raw);
            } else {
                $ids = (array) $raw;
            }
            $ids = array_filter(array_map('intval', $ids));
            $productNames = Product::whereIn('id', $ids)->pluck('name')->toArray();
        }

        $statusHtml = match ($record->status) {
            'pending' => '<span style="color: #f59e0b; font-weight: 600;">Pendiente de aprobación</span>',
            'approved' => '<span style="color: #10b981; font-weight: 600;">Aprobada</span>',
            'rejected' => '<span style="color: #ef4444; font-weight: 600;">Rechazada</span>',
            default => ucfirst($record->status),
        };

        $schema = [
            Section::make('Estado de la reserva')
                ->icon('heroicon-o-information-circle')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('status_display')
                            ->label('Estado')
                            ->content(new HtmlString($statusHtml)),
                        Placeholder::make('created_display')
                            ->label('Fecha de solicitud')
                            ->content($record->created_at->format('d/m/Y H:i')),
                        Placeholder::make('updated_display')
                            ->label('Última actualización')
                            ->content($record->updated_at->format('d/m/Y H:i')),
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
                            ->label('Nombre de la investigación')
                            ->content($record->research_name ?? 'No especificado'),
                        Placeholder::make('advisor_display')
                            ->label('Asesor')
                            ->content($record->advisor ?? 'No especificado'),
                    ]),
                    Placeholder::make('applicants_display')
                        ->label('Solicitantes')
                        ->content($record->applicants ?: 'No especificado'),
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
            'index' => Pages\ListReservationHistories::route('/'),
        ];
    }
}
