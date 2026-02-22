<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Models\AcademicProgram;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Reserva de Espacio';

    protected static ?string $navigationLabel = 'Reservar Espacio';

    protected static ?string $navigationGroup = 'Gestion de Reservas';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user
          && ! $user->hasRole('LABORATORISTA')
          && ! $user->hasRole('COORDINADOR');
    }

    public static function table(Table $table): Table
    {
        $today = Carbon::now()->startOfDay();
        $limit = Carbon::now()->addMonth()->endOfDay();

        return $table
            ->query(
                Schedule::where('type', 'unstructured')
                    ->whereBetween('start_at', [$today, $limit])
                    ->orderBy('start_at')
                    ->with(['laboratory', 'booking' => function ($query) {
                        $query->where('status', 'approved');
                    }])
                    ->withCount(['booking' => function ($query) {
                        $query->where('status', 'approved');
                    }])
            )
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label('Espacio Académico')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(
                        fn (Schedule $record): string => $record->booking_count > 0 ? 'gray' : 'success'
                    )
                    ->formatStateUsing(
                        fn (Schedule $record) => $record->laboratory->name.
                          ($record->booking_count > 0 ? ' (Ocupado)' : ' (Libre)')
                    ),

                TextColumn::make('start_at')
                    ->label('Inicio')
                    ->sortable()
                    ->formatStateUsing(
                        fn (string $state): string => Carbon::parse($state)->locale('es')->translatedFormat('l, d \d\e F \d\e Y - g:i A')
                    ),

                TextColumn::make('end_at')
                    ->label('Fin')
                    ->sortable()
                    ->formatStateUsing(
                        fn (string $state): string => Carbon::parse($state)->locale('es')->translatedFormat('l, d \d\e F \d\e Y - g:i A')
                    ),
            ])
            ->filters([
                SelectFilter::make('laboratory')
                    ->label('Espacio Académico')
                    ->relationship('laboratory', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('availability')
                    ->label('Disponibilidad')
                    ->options([
                        'available' => 'Libres',
                        'occupied' => 'Ocupados',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'available') {
                            $query->having('booking_count', 0);
                        } elseif ($data['value'] === 'occupied') {
                            $query->having('booking_count', '>', 0);
                        }
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions([
                TableAction::make('reservar')
                    ->label('Reservar')
                    ->button()
                    ->disabled(
                        fn (Schedule $record): bool => $record->booking_count > 0
                    )
                    ->modalHeading('Solicitud de Reserva')
                    ->modalDescription('Completa los datos para solicitar este espacio.')
                    ->modalWidth('2xl')
                    ->form([
                        Section::make('Espacio y horario seleccionado')
                            ->icon('heroicon-o-calendar-days')
                            ->collapsed(false)
                            ->compact()
                            ->schema([
                                Hidden::make('laboratory_id')
                                    ->default(fn (Schedule $record) => $record->laboratory_id)
                                    ->required(),
                                Hidden::make('start_at')
                                    ->default(fn (Schedule $record) => $record->start_at),
                                Hidden::make('end_at')
                                    ->default(fn (Schedule $record) => $record->end_at),
                                Grid::make(3)->schema([
                                    Placeholder::make('laboratory_display')
                                        ->label('Espacio académico')
                                        ->content(fn (Schedule $record) => $record->laboratory->name ?? 'No asignado'),
                                    Placeholder::make('start_display')
                                        ->label('Inicio')
                                        ->content(fn (Schedule $record) => Carbon::parse($record->start_at)->locale('es')->translatedFormat('D d M Y - g:i A')),
                                    Placeholder::make('end_display')
                                        ->label('Fin')
                                        ->content(fn (Schedule $record) => Carbon::parse($record->end_at)->locale('es')->translatedFormat('D d M Y - g:i A')),
                                ]),
                            ]),

                        Section::make('Información del proyecto')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Radio::make('project_type')
                                    ->label('Tipo de proyecto')
                                    ->options([
                                        'Trabajo de grado' => 'Trabajo de grado',
                                        'Investigación profesoral' => 'Investigación profesoral',
                                    ])
                                    ->inline()
                                    ->required(),
                                Grid::make(2)->schema([
                                    Select::make('academic_program')
                                        ->label('Programa académico')
                                        ->options(fn () => AcademicProgram::where('is_active', true)
                                            ->orderBy('name')
                                            ->pluck('name', 'name'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('semester')
                                        ->label('Semestre')
                                        ->options(array_combine(range(1, 10), range(1, 10)))
                                        ->required(),
                                ]),
                                TextInput::make('research_name')
                                    ->label('Nombre de la investigación')
                                    ->placeholder('Ej: Análisis de muestras de suelo')
                                    ->required(),
                            ]),

                        Section::make('Participantes')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Select::make('applicants')
                                    ->label('Solicitantes')
                                    ->helperText('Busca por nombre, apellido o correo.')
                                    ->multiple()
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                    ->required(),
                                Select::make('advisor')
                                    ->label('Asesor')
                                    ->helperText('Busca por nombre, apellido o correo.')
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                    ->required(),
                            ]),

                        Section::make('Materiales y equipos')
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Select::make('products')
                                    ->label('Productos requeridos')
                                    ->helperText('Selecciona los materiales o equipos que necesitas.')
                                    ->multiple()
                                    ->searchable()
                                    ->options(fn () => cache()->remember('products-for-booking', 300, fn () => Product::with('laboratory')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->name} — {$p->laboratory->name}"])->toArray()))
                                    ->required(),
                            ]),
                    ])
                    ->action(function (Schedule $record, array $data): void {
                        $user = Auth::user();
                        $applicantNames = User::whereIn('id', $data['applicants'])->get()->map(fn ($user) => "{$user->name} {$user->last_name}")->implode(', ');
                        $advisorUser = User::find($data['advisor']);
                        $advisorName = $advisorUser ? "{$advisorUser->name} {$advisorUser->last_name}" : '';
                        Booking::create([
                            'schedule_id' => $record->id,
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'project_type' => $data['project_type'],
                            'laboratory_id' => $data['laboratory_id'],
                            'academic_program' => $data['academic_program'],
                            'semester' => $data['semester'],
                            'applicants' => $applicantNames,
                            'research_name' => $data['research_name'],
                            'advisor' => $advisorName,
                            'products' => $data['products'],
                            'start_at' => $data['start_at'],
                            'end_at' => $data['end_at'],
                            'status' => Booking::STATUS_PENDING,
                        ]);
                    })
                    ->successRedirectUrl(url()->previous())
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('¡Solicitud Exitosa!')
                            ->body('Tu reserva ha sido enviada y está pendiente de aprobación.')
                            ->duration(5005)
                    ),
            ]);
    }

    public static function getWidgets(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
        ];
    }
}
