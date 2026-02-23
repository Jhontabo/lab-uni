<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Laboratory;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Inventario';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralLabel = 'Productos';

    protected static ?int $navigationSort = 101;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'success' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Tabs::make('Información del Producto')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Datos Básicos')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('name')
                                        ->label('Nombre del Producto')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Ej: Microscopio Digital')
                                        ->helperText('Nombre corto y descriptivo')
                                        ->columnSpan(2),

                                    TextInput::make('serial_number')
                                        ->label('Número de Serie')
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->helperText('Opcional: identificador único del equipo'),

                                    Select::make('product_type')
                                        ->label('Tipo')
                                        ->options([
                                            'equipment' => 'Equipo',
                                            'supply' => 'Suministro',
                                        ])
                                        ->required()
                                        ->live(),

                                    TextInput::make('unit_cost')
                                        ->label('Costo Unitario')
                                        ->required()
                                        ->numeric()
                                        ->prefix('$')
                                        ->step(0.01),

                                    Select::make('laboratory_id')
                                        ->label('Laboratorio')
                                        ->options(Laboratory::all()->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText('Ubicación donde se guarda el equipo'),

                                    TextInput::make('use')
                                        ->label('Uso del equipo')
                                        ->placeholder('Ej: Prácticas de laboratorio'),

                                    Select::make('status')
                                        ->label('Condición')
                                        ->options([
                                            'new' => 'Nuevo',
                                            'used' => 'Buen Estado',
                                            'damaged' => 'Dañado',
                                            'decommissioned' => 'Fuera de Servicio',
                                        ])
                                        ->required()
                                        ->default('new'),

                                    Toggle::make('available_for_loan')
                                        ->label('Disponible para Préstamo')
                                        ->default(true)
                                        ->helperText('Permitir que estudiantes soliciten este equipo'),
                                ]),
                            ]),

                        Tab::make('Inventario')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('available_quantity')
                                        ->label('Cantidad en Inventario')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(1),

                                    DatePicker::make('acquisition_date')
                                        ->label('Fecha de Adquisición')
                                        ->displayFormat('d/m/Y')
                                        ->maxDate(now()),
                                ]),

                                Textarea::make('observations')
                                    ->label('Observaciones')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->placeholder('Notas adicionales sobre el equipo...'),
                            ]),

                        Tab::make('Técnico')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('brand')->label('Marca'),
                                    TextInput::make('model')->label('Modelo'),
                                    TextInput::make('manufacturer')->label('Fabricante'),
                                    TextInput::make('dimensions')->label('Dimensiones'),
                                    TextInput::make('weight')->label('Peso'),
                                    TextInput::make('power')->label('Potencia'),
                                ]),

                                TagsInput::make('accessories')
                                    ->label('Accesorios')
                                    ->columnSpanFull()
                                    ->helperText('Presiona Enter para agregar cada accesorio'),
                            ]),

                        Tab::make('Imagen')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Imagen del Producto')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('products/images')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Imagen representativa (max 2MB). Formatos: JPG, PNG')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Gestión de Inventario')
            ->description('Administra los equipos y materiales del laboratorio')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->size(50)
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->product_type === 'equipment'
                      ? asset('images/default-equipment.png')
                      : asset('images/default-supply.png')),

                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->wrap()
                    ->description(fn (Product $record) => $record->brand ? "{$record->brand} • {$record->model}" : ($record->serial_number ?? 'Sin série'), 'after'),

                TextColumn::make('laboratory.name')
                    ->label('Ubicación')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-map-pin'),

                TextColumn::make('available_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->available_quantity <= 5) {
                            return 'danger';
                        }

                        return 'success';
                    })
                    ->icon(function ($record) {
                        if ($record->available_quantity <= 5) {
                            return 'heroicon-o-exclamation-triangle';
                        }

                        return 'heroicon-o-check-circle';
                    })
                    ->iconPosition(IconPosition::Before),

                TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'equipment' => 'info',
                        'supply' => 'success',
                        'chemical' => 'warning',
                        'glassware' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'equipment' => 'Equipo',
                        'supply' => 'Suministro',
                        'chemical' => 'Reactivo',
                        'glassware' => 'Vidriería',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'success',
                        'used' => 'info',
                        'used_worn' => 'warning',
                        'damaged', 'decommissioned', 'lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nuevo',
                        'used' => 'Buen Estado',
                        'used_worn' => 'Desgaste',
                        'damaged' => 'Dañado',
                        'decommissioned' => 'Inactivo',
                        'lost' => 'Perdido',
                        default => $state,
                    })
                    ->sortable(),

                ToggleColumn::make('available_for_loan')
                    ->label('Préstamo')
                    ->onColor('success')
                    ->offColor('gray')
                    ->sortable()
                    ->alignCenter()
                    ->tooltip(fn (Product $record) => $record->available_for_loan ? 'Disponible para préstamo' : 'No disponible'),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipo')
                    ->options([
                        'equipment' => 'Equipo',
                        'supply' => 'Suministro',
                        'chemical' => 'Reactivo Químico',
                        'glassware' => 'Material de Vidrio',
                    ])
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'new' => 'Nuevo',
                        'used' => 'Buen Estado',
                        'used_worn' => 'Desgaste Normal',
                        'damaged' => 'Dañado',
                        'decommissioned' => 'Inactivo',
                        'lost' => 'Perdido',
                    ])
                    ->multiple(),

                SelectFilter::make('laboratory_id')
                    ->label('Laboratorio')
                    ->options(Laboratory::all()->pluck('name', 'id'))
                    ->searchable()
                    ->multiple(),

                TernaryFilter::make('available_for_loan')
                    ->label('Préstamo')
                    ->trueLabel('Disponibles')
                    ->falseLabel('No disponibles'),

                Filter::make('low_stock')
                    ->label('⚠️ Stock Bajo')
                    ->query(fn (Builder $query): Builder => $query->where('available_quantity', '<=', 5))
                    ->default(false),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver')
                        ->tooltip('Ver detalles')
                        ->icon('heroicon-o-eye')
                        ->color('gray'),

                    EditAction::make()
                        ->label('Editar')
                        ->tooltip('Editar')
                        ->icon('heroicon-o-pencil')
                        ->color('gray'),
                ])
                    ->dropdown(true)
                    ->label('Acciones')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary'),

                ActionGroup::make([
                    Action::make('loan_history')
                        ->label('Historial de Préstamos')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('info')
                        ->modalHeading(fn (Product $record) => "Préstamos del equipo: {$record->name}")
                        ->modalContent(fn (Product $record) => view(
                            'filament.pages.loan-history-modal',
                            [
                                'loans' => \App\Models\Loan::where('product_id', $record->id)
                                    ->with(['user'])
                                    ->orderBy('requested_at', 'desc')
                                    ->limit(50)
                                    ->get(),
                            ]
                        ))
                        ->modalWidth('6xl')
                        ->modalSubmitAction(false),

                    Action::make('duplicate')
                        ->label('Duplicar Producto')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->action(function (Product $record) {
                            $newProduct = $record->replicate();
                            $newProduct->name = $record->name.' (Copia)';
                            $newProduct->serial_number = null;
                            $newProduct->available_quantity = 1;
                            $newProduct->save();

                            Notification::make()
                                ->title('Producto duplicado')
                                ->body("Se creó una copia: {$newProduct->name}")
                                ->success()
                                ->send();
                        }),

                    Action::make('history')
                        ->label('Historial de Bajas')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->modalHeading(fn (Product $record) => "Historial del equipo: {$record->name}")
                        ->modalContent(fn (Product $record) => view(
                            'filament.pages.history-modal-product-resource',
                            [
                                'history' => $record->equipmentDecommissions()
                                    ->with(['registeredBy', 'reversedBy'])
                                    ->orderBy('created_at', 'desc')
                                    ->get(),
                            ]
                        ))
                        ->modalWidth('8xl')
                        ->modalSubmitAction(false)
                        ->hidden(fn (Product $record) => $record->product_type !== 'equipment'),

                    DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-chevron-down')
                    ->color('gray')
                    ->size('sm'),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                BulkAction::make('markAsLost')
                    ->label('Marcar como Perdido')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->action(fn (Collection $records) => $records->each->update(['status' => 'lost', 'available_for_loan' => false]))
                    ->requiresConfirmation()
                    ->modalHeading('Marcar productos seleccionados como perdidos')
                    ->modalDescription('¿Está seguro de marcar estos productos como perdidos?')
                    ->modalSubmitActionLabel('Sí, marcar como perdidos'),

                BulkAction::make('decommissionSelected')
                    ->label('Dar de Baja')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->form([
                        Select::make('decommission_type')
                            ->label('Tipo de baja')
                            ->options([
                                'damaged' => 'Dañado',
                                'maintenance' => 'Mantenimiento',
                                'other' => 'Otra razón',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('damage_type', null);
                                $set('responsible_user_id', null);
                                $set('academic_program', null);
                                $set('semester', null);
                            }),

                        // Nuevo campo para tipo de daño (solo visible cuando es 'damaged')
                        Select::make('damage_type')
                            ->label('Tipo de daño')
                            ->options([
                                'student' => 'Dañado por estudiante',
                                'usage' => 'Deterioro por uso normal',
                                'manufacturing' => 'Defecto de fabricación',
                                'other' => 'Otra causa',
                            ])
                            ->required(fn (callable $get) => $get('decommission_type') === 'damaged')
                            ->visible(fn (callable $get) => $get('decommission_type') === 'damaged')
                            ->live(),

                        // Grupo de campos solo visibles cuando el daño es por estudiante
                        Fieldset::make('Información del Estudiante Responsable')
                            ->visible(
                                fn (callable $get) => $get('decommission_type') === 'damaged' &&
                                  $get('damage_type') === 'student'
                            )
                            ->schema([
                                Select::make('responsible_user_id')
                                    ->label('Estudiante')
                                    ->options(function () {
                                        return User::whereHas('roles', fn ($q) => $q->where('name', 'estudiante'))
                                            ->get()
                                            ->mapWithKeys(fn ($user) => [
                                                $user->id => sprintf(
                                                    '%s %s - %s',
                                                    $user->name ?? 'Sin nombre',
                                                    $user->last_name ?? '',
                                                    $user->document_number ?? 'Sin documento'
                                                ),
                                            ]);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $user = User::find($state);
                                            $set('academic_program', $user->academic_program ?? null);
                                            $set('semester', $user->semester ?? null);
                                        }
                                    }),

                                Select::make('academic_program')
                                    ->label('Programa académico')
                                    ->options(function (callable $get) {
                                        $programs = [
                                            'Ingeniería de Sistemas' => 'Ingeniería de Sistemas',
                                            'Ingeniería Civil' => 'Ingeniería Civil',
                                        ];

                                        if ($userId = $get('responsible_user_id')) {
                                            $userProgram = User::find($userId)?->academic_program;
                                            if ($userProgram && ! array_key_exists($userProgram, $programs)) {
                                                $programs[$userProgram] = $userProgram;
                                            }
                                        }

                                        return $programs;
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive(),

                                Select::make('semester')
                                    ->label('Semestre')
                                    ->options(function (callable $get) {
                                        $semesters = collect(range(2, 10))->mapWithKeys(fn ($i) => [$i => "Semestre $i"]);

                                        if ($userId = $get('responsible_user_id')) {
                                            $userSemester = User::find($userId)?->semester;
                                            if ($userSemester && ! $semesters->has($userSemester)) {
                                                $semesters->put($userSemester, "Semestre $userSemester");
                                            }
                                        }

                                        return $semesters;
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive(),
                            ]),

                        Textarea::make('observations')
                            ->label('Descripción detallada')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(501),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            \App\Models\EquipmentDecommission::create([
                                'product_id' => $record->id,
                                'reason' => $data['decommission_type'],
                                'damage_type' => $data['decommission_type'] === 'damaged'
                                  ? $data['damage_type']
                                  : null,
                                'responsible_user_id' => $data['decommission_type'] === 'damaged' &&
                                  $data['damage_type'] === 'student'
                                  ? $data['responsible_user_id']
                                  : null,
                                'academic_program' => $data['decommission_type'] === 'damaged' &&
                                  $data['damage_type'] === 'student'
                                  ? $data['academic_program']
                                  : null,
                                'semester' => $data['decommission_type'] === 'damaged' &&
                                  $data['damage_type'] === 'student'
                                  ? $data['semester']
                                  : null,
                                'decommission_date' => now(),
                                'registered_by' => auth()->id(),
                                'observations' => $data['observations'],
                            ]);

                            $record->update([
                                'status' => 'decommissioned',
                                'available_for_loan' => false,
                                'decommissioned_at' => now(),
                                'decommissioned_by' => auth()->id(),
                            ]);
                        }

                        Notification::make()
                            ->title('Baja registrada exitosamente')
                            ->body("Se dieron de baja {$records->count()} equipos.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar baja de equipos')
                    ->modalDescription('Esta acción registrará la baja de los equipos seleccionados. ¿Desea continuar?')
                    ->modalSubmitActionLabel('Confirmar baja'),

                DeleteBulkAction::make()
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Crear Nuevo Producto')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('name', 'asc')
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->striped()
            ->groups([
                'laboratory.name',
                'product_type',
                'status',
            ])
            ->groupingSettingsInDropdownOnDesktop()
            ->groupRecordsTriggerAction(
                fn (Action $action) => $action
                    ->label('Agrupar registros')
                    ->icon('heroicon-o-bars-arrow-down')
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
