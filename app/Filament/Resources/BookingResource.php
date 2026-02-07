<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Reserva de Espacio';

    protected static ?string $navigationLabel = 'Reservar Espacio';

    protected static ?string $navigationGroup = 'Gestion de Reservas';

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
                    ->with(['laboratory', 'booking'])
            )
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label('Espacio Académico')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(
                        fn (Schedule $record): string => $record->booking->where('status', 'approved')->isNotEmpty() ? 'gray' : 'success'
                    )
                    ->formatStateUsing(
                        fn (Schedule $record) => $record->laboratory->name.
                          ($record->booking->where('status', 'approved')->isNotEmpty() ? ' (Ocupado)' : ' (Libre)')
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
            ->actions([
                TableAction::make('reservar')
                    ->label('Reservar')
                    ->button()
                  // ✅ CAMBIO 3: El botón solo se desactiva si hay una reserva 'approved'
                    ->disabled(
                        fn (Schedule $record): bool => $record->booking->where('status', 'approved')->isNotEmpty()
                    )
                    ->modalHeading('Solicitud de Reserva')
                    ->modalWidth('lg')
                    ->form([
                        // Tu formulario no cambia...
                        Section::make('Detalles de la práctica')->schema([
                            Radio::make('project_type')
                                ->label('Tipo de proyecto')
                                ->options([
                                    'Trabajo de grado' => 'Trabajo de grado',
                                    'Investigación profesoral' => 'Investigación profesoral',
                                ])->columns(5)->required(),
                            Placeholder::make('laboratory_display')
                                ->label('Espacio académico')
                                ->content(fn (Schedule $record) => $record->laboratory->name ?? 'No asignado'),
                            Hidden::make('laboratory_id')
                                ->default(fn (Schedule $record) => $record->laboratory_id)->required(),

                            Select::make('academic_program')
                                ->label('Programa académico')
                                ->options([
                                    'Derecho' => 'Derecho',
                                    'Trabajo Social' => 'Trabajo Social',
                                    'Comunicación Social' => 'Comunicación Social',
                                    'Psicología' => 'Psicología',
                                    'Mercadeo' => 'Mercadeo',
                                    'Contaduría Pública' => 'Contaduría Pública',
                                    'Administración de Negocios Internacionales' => 'Administración de Negocios Internacionales',
                                    'Licenciatura en Teología' => 'Licenciatura en Teología',
                                    'Licenciatura en Educación Infantil' => 'Licenciatura en Educación Infantil',
                                    'Licenciatura en Educación Básica Primaria' => 'Licenciatura en Educación Básica Primaria',
                                    'Enfermería' => 'Enfermería',
                                    'Terapia Ocupacional' => 'Terapia Ocupacional',
                                    'Fisioterapia' => 'Fisioterapia',
                                    'Nutrición y Dietética' => 'Nutrición y Dietética',
                                    'Ingeniería Mecatrónica' => 'Ingeniería Mecatrónica',
                                    'Ingeniería Civil' => 'Ingeniería Civil',
                                    'Ingeniería de Sistemas' => 'Ingeniería de Sistemas',
                                    'Ingeniería Ambiental' => 'Ingeniería Ambiental',
                                    'Ingeniería de Procesos' => 'Ingeniería de Procesos',
                                ])
                                ->required(),

                            Select::make('semester')
                                ->label('Semestre')
                                ->options(array_combine(range(1, 10), range(1, 10)))->required(),
                            Select::make('applicants')
                                ->label('Nombre de los solicitantes')
                                ->multiple()->searchable()
                                ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->limit(13)->get()->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                ->required(),
                            TextInput::make('research_name')
                                ->label('Nombre de la investigación')->required(),
                            Select::make('advisor')
                                ->label('Nombre del asesor')
                                ->searchable()
                                ->getSearchResultsUsing(fn (string $search) => User::where('name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->limit(16)->get()->mapWithKeys(fn ($user) => [$user->id => "{$user->name} {$user->last_name} - {$user->email}"]))
                                ->required(),
                        ]),
                        Section::make('Materiales y equipos')->schema([
                            Select::make('products')
                                ->label('Productos disponibles')
                                ->multiple()->searchable()
                                ->options(fn () => Product::with('laboratory')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->name} — {$p->laboratory->name}"])->toArray())
                                ->required(),
                        ]),
                        Section::make('Horario solicitado')->schema([
                            DateTimePicker::make('start_at')
                                ->label('Inicio')
                                ->default(fn (Schedule $record) => $record->start_at)->readOnly(),
                            DateTimePicker::make('end_at')
                                ->label('Fin')
                                ->default(fn (Schedule $record) => $record->end_at)->after('start_at')->readOnly(),
                        ]),
                    ])
                    ->action(function (Schedule $record, array $data): void {
                        $user = Auth::user();
                        $applicantNames = User::whereIn('id', $data['applicants'])->get()->map(fn ($user) => "{$user->name} {$user->last_name}")->implode(', ');
                        $advisorUser = User::find($data['advisor']);
                        $advisorName = $advisorUser ? "{$advisorUser->name} {$advisorUser->last_name}" : '';
                        $productsJson = json_encode($data['products']);
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
                            'products' => $productsJson,
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
