<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Laboratory;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Builder as FormBuilder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Tab;
use Filament\Forms\Components\View;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Colors\Color;
use Filament\Tables\Enums\ActionsPosition;

class ProductResource extends Resource
{
  protected static ?string $model = Product::class;


  protected static ?string $navigationLabel = 'Inventario';
  protected static ?string $navigationGroup = 'Inventario';
  protected static ?string $modelLabel = 'Producto';
  protected static ?string $pluralLabel = 'Productos';
  protected static ?int $navigationSort = 1;

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
    return $form->schema([
      Wizard::make([
        Step::make('Información Básica')
          ->icon('heroicon-o-information-circle')
          ->schema([
            Grid::make(3)->schema([
              TextInput::make('name')
                ->label('Nombre del Producto')
                ->required()
                ->maxLength(255)
                ->placeholder('Ej: Microscopio Digital'),

              TextInput::make('serial_number')
                ->label('Número de Serie / Identificación')
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('Dejar vacío si no aplica'),

              TextInput::make('use')
                ->label('Uso del equipo')
                ->placeholder('Ej: Prácticas de laboratorio, investigación'),

              TextInput::make('unit_cost')
                ->label('Costo Unitario')
                ->required()
                ->numeric()
                ->minValue(0)
                ->maxValue(999999999999.99)
                ->prefix('$')
                ->step(0.01),

              Select::make('laboratory_id')
                ->label('Laboratorio Asignado')
                ->options(Laboratory::all()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

              CheckboxList::make('applies_to')
                ->label('Proceso al que aplica')
                ->options([
                  'investigacion' => 'Investigación',
                  'docencia' => 'Docencia',
                ])
                ->columns(2),

              CheckboxList::make('authorized_personnel')
                ->label('Personal autorizado para el uso')
                ->options([
                  'laboratorista' => 'Laboratorista',
                  'monitor' => 'Monitor',
                  'profesor' => 'Profesor',
                  'investigador' => 'Investigador',
                ])
                ->columns(2),

              Select::make('product_type')
                ->label('Tipo de Producto')
                ->options([
                  'equipment' => 'Equipo',
                  'supply' => 'Suministro',
                ])
                ->required(),
            ]),
          ]),

        Step::make('Especificaciones Técnicas')
          ->icon('heroicon-o-clipboard-document-list')
          ->schema([
            Grid::make(3)->schema([
              TextInput::make('brand')->label('Marca')->maxLength(255),
              TextInput::make('model')->label('Modelo')->maxLength(255),
              TextInput::make('manufacturer')->label('Fabricante')->maxLength(255),
              Select::make('status')
                ->label('Condición Actual')
                ->options([
                  'new' => 'Nuevo',
                  'used' => 'Usado',
                  'damaged' => 'Dañado',
                  'decommissioned' => 'Fuera de Servicio',
                  'lost' => 'Perdido',
                  'maintenance' => 'Mantenimiento',
                ])
                ->required(),
              Select::make('calibration_frequency')
                ->label('Frecuencia de calibración')
                ->options([
                  'semanal' => 'Semanal',
                  'mensual' => 'Mensual',
                  'semestral' => 'Semestral',
                  'anual' => 'Anual',
                ]),

              Toggle::make('available_for_loan')
                ->label('Disponible para Préstamo')
                ->default(true),
            ]),
          ]),

        Step::make('Condiciones de Uso y Observaciones')
          ->icon('heroicon-o-adjustments-vertical')
          ->schema([
            Section::make('Condiciones Tolerables')
              ->description('Valores recomendados de operación para este equipo')
              ->schema([
                Grid::make(3)->schema([
                  TextInput::make('min_temperature')
                    ->label('Temp. Mínima (°C)')
                    ->numeric()
                    ->minValue(-80)
                    ->maxValue(200)
                    ->helperText('Dejar vacío si no aplica'),
                  TextInput::make('max_temperature')
                    ->label('Temp. Máxima (°C)')
                    ->numeric()
                    ->minValue(-80)
                    ->maxValue(200)
                    ->helperText('Dejar vacío si no aplica'),
                  TextInput::make('min_humidity')
                    ->label('Humedad Mín. (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Dejar vacío si no aplica'),
                  TextInput::make('max_humidity')
                    ->label('Humedad Máx. (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText('Dejar vacío si no aplica'),
                  TextInput::make('min_voltage')
                    ->label('Voltaje Mín. (V)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(10000)
                    ->helperText('Dejar vacío si no aplica'),
                  TextInput::make('max_voltage')
                    ->label('Voltaje Máx. (V)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(10000)
                    ->helperText('Dejar vacío si no aplica'),
                ]),
              ]),
            Textarea::make('observations')
              ->label('Observaciones')
              ->maxLength(500)
              ->rows(3),
          ]),

        Step::make('Datos Específicos del Equipo')
          ->icon('heroicon-o-cog-6-tooth')
          ->schema([
            Section::make('Datos Técnicos y Accesorios')
              ->description('Información técnica, documentación y accesorios asociados')
              ->schema([
                Grid::make(2)->schema([
                  TextInput::make('upper_measure')->label('Medida Superior')->maxLength(255),
                  TextInput::make('lower_measure')->label('Medida Inferior')->maxLength(255),
                  TextInput::make('associated_software')->label('Software Asociado')->maxLength(255),
                  Select::make('user_manual')
                    ->label('Manual de Usuario')
                    ->options([
                      'fisico' => 'Físico',
                      'digital' => 'Digital',
                    ])
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                      if ($state !== 'digital') {
                        $set('manual_url', null);
                      }
                    })
                    ->helperText('Seleccione si el manual es físico o digital'),
                  // Campo dinámico: solo aparece si selecciona "digital"
                  TextInput::make('manual_url')
                    ->label('Enlace del manual digital')
                    ->url()
                    ->placeholder('https://...')
                    ->maxLength(255)
                    ->visible(fn(callable $get) => $get('user_manual') === 'digital')
                    ->required(fn(callable $get) => $get('user_manual') === 'digital'),
                  TextInput::make('dimensions')->label('Dimensiones')->maxLength(255),
                  TextInput::make('weight')->label('Peso')->maxLength(50),
                  TextInput::make('power')->label('Potencia')->maxLength(255),
                ]),
                TagsInput::make('accessories')->label('Accesorios'),
              ]),
          ]),
        Step::make('Documentación e Imágenes')
          ->icon('heroicon-o-photo')
          ->schema([
            Grid::make(3)->schema([
              TextInput::make('available_quantity')
                ->label('Cantidad en Inventario')
                ->required()
                ->numeric()
                ->minValue(0)
                ->step(1),

              DatePicker::make('acquisition_date')
                ->label('Fecha de Adquisición')
                ->displayFormat('d/m/Y')
                ->maxDate(now()),

            ]),

            FileUpload::make('image')
              ->label('Imagen Principal')
              ->image()
              ->imageEditor()
              ->imageEditorAspectRatios([null, '16:9', '4:3', '1:1'])
              ->directory('products/images')
              ->disk('public')
              ->visibility('public')
              ->maxSize(2048)
              ->openable()
              ->downloadable()
              ->previewable()
              ->helperText('Imagen representativa del producto (max 2MB)')
              ->columnSpanFull(),
          ]),

      ])
        ->persistStepInQueryString()
        ->columnSpanFull(),
    ]);
  }


  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        ImageColumn::make('image')
          ->label('')
          ->size(60)
          ->circular()
          ->defaultImageUrl(fn($record) => $record->product_type === 'equipment'
            ? asset('images/default-equipment.png')
            : asset('images/default-supply.png')),

        TextColumn::make('name')
          ->label('Producto')
          ->searchable()
          ->sortable()
          ->weight(FontWeight::Bold)
          ->wrap()
          ->tooltip(function (Product $record) {
            return "Descripción completa: " . $record->description;
          }),

        TextColumn::make('available_quantity')
          ->label('Stock')
          ->numeric()
          ->sortable()
          ->color(function ($record) {
            // Cambia ($record->minimum_stock ?? 0) por el valor fijo que uses
            if ($record->available_quantity <= 5) { // Mismo valor que en el filtro
              return 'danger';
            }
            return 'success';
          })
          ->icon(function ($record) {
            if ($record->available_quantity <= 5) { // Mismo valor que en el filtro
              return 'heroicon-o-exclamation-triangle';
            }
            return null;
          })
          ->iconPosition(IconPosition::After),

        TextColumn::make('product_type')
          ->label('Tipo')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'equipment' => 'info',
            'supply' => 'success',
            'chemical' => 'warning',
            'glassware' => 'primary',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state): string => match ($state) {
            'equipment' => 'Equipo',
            'supply' => 'Suministro',
            'chemical' => 'Reactivo',
            'glassware' => 'Vidriería',
            default => $state,
          })
          ->sortable(),

        TextColumn::make('status')
          ->label('Condición')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'new' => 'success',
            'used' => 'info',
            'used_worn' => 'warning',
            'damaged', 'decommissioned', 'lost' => 'danger',
            default => 'gray',
          })
          ->formatStateUsing(fn(string $state): string => match ($state) {
            'new' => 'Nuevo',
            'used' => 'Buen Estado',
            'used_worn' => 'Desgaste',
            'damaged' => 'Dañado',
            'decommissioned' => 'Inactivo',
            'lost' => 'Perdido',
            default => $state,
          })
          ->sortable(),

        TextColumn::make('unit_cost')
          ->label('Costo Unitario')
          ->money('COP')
          ->sortable(),
        ToggleColumn::make('available_for_loan')
          ->label('Préstamo')
          ->onColor('success')
          ->offColor('danger')
          ->sortable()
          ->alignCenter(),

        TextColumn::make('laboratory.name')
          ->label('Ubicación')
          ->searchable()
          ->sortable()
          ->badge()
          ->color('info'),
      ])
      ->filters([
        SelectFilter::make('product_type')
          ->label('Tipo de Producto')
          ->options([
            'equipment' => 'Equipo',
            'supply' => 'Suministro',
            'chemical' => 'Reactivo Químico',
            'glassware' => 'Material de Vidrio',
          ])
          ->multiple()
          ->searchable(),

        SelectFilter::make('status')
          ->label('Condición')
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
          ->label('Disponible para Préstamo')
          ->trueLabel('Solo disponibles')
          ->falseLabel('No disponibles'),

        Filter::make('low_stock')
          ->label('Stock Bajo')
          ->query(fn(Builder $query): Builder => $query->whereColumn('available_quantity', '<=', 'minimum_stock'))
          ->default(false),
      ], layout: FiltersLayout::AboveContentCollapsible)
      ->actions([
        ActionGroup::make([
          ViewAction::make()
            ->icon('heroicon-o-eye')
            ->color('info'),
          EditAction::make()
            ->icon('heroicon-o-pencil')
            ->color('warning'),
          DeleteAction::make()
            ->icon('heroicon-o-trash')
            ->color('danger'),

          Action::make('history')
            ->label('Historial')
            ->icon('heroicon-o-clock')
            ->modalHeading(fn(Product $record) => "Historial del equipo: {$record->name}")
            ->modalContent(fn(Product $record) => view(
              'filament.pages.history-modal-product-resource',
              [
                'history' => $record->equipmentDecommissions()
                  ->with(['registeredBy', 'reversedBy'])
                  ->orderBy('created_at', 'desc')
                  ->get()
              ]
            ))
            ->modalWidth('8xl')
            ->modalSubmitAction(false)
            ->hidden(fn(Product $record) => $record->product_type !== 'equipment')


        ])
          ->tooltip('Acciones')
          ->icon('heroicon-s-cog-6-tooth')
          ->color('primary'),
      ], position: ActionsPosition::BeforeCells)
      ->bulkActions([
        BulkAction::make('markAsLost')
          ->label('Marcar como Perdido')
          ->icon('heroicon-o-shield-exclamation')
          ->color('warning')
          ->action(fn(Collection $records) => $records->each->update(['status' => 'lost', 'available_for_loan' => false,]))
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
                'other' => 'Otra razón'
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
                'other' => 'Otra causa'
              ])
              ->required(fn(callable $get) => $get('decommission_type') === 'damaged')
              ->visible(fn(callable $get) => $get('decommission_type') === 'damaged')
              ->live(),

            // Grupo de campos solo visibles cuando el daño es por estudiante
            Fieldset::make('Información del Estudiante Responsable')
              ->visible(
                fn(callable $get) =>
                $get('decommission_type') === 'damaged' &&
                  $get('damage_type') === 'student'
              )
              ->schema([
                Select::make('responsible_user_id')
                  ->label('Estudiante')
                  ->options(function () {
                    return User::whereHas('roles', fn($q) => $q->where('name', 'estudiante'))
                      ->get()
                      ->mapWithKeys(fn($user) => [
                        $user->id => sprintf(
                          "%s %s - %s",
                          $user->name ?? 'Sin nombre',
                          $user->last_name ?? '',
                          $user->document_number ?? 'Sin documento'
                        )
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
                      if ($userProgram && !array_key_exists($userProgram, $programs)) {
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
                    $semesters = collect(range(2, 10))->mapWithKeys(fn($i) => [$i => "Semestre $i"]);

                    if ($userId = $get('responsible_user_id')) {
                      $userSemester = User::find($userId)?->semester;
                      if ($userSemester && !$semesters->has($userSemester)) {
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
        fn(Action $action) => $action
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
