<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaboratoryResource\Pages;
use App\Models\Laboratory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LaboratoryResource extends Resource
{
    protected static ?string $model = Laboratory::class;

    // protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Laboratorios';

    protected static ?string $navigationGroup = 'Laboratorios';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Laboratorios';

    protected static ?string $modelLabel = 'Laboratorio';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion del laboratorio')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo. Quimica'),

                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->placeholder('Ejem. 20')
                                    ->helperText('Numero maximo de personas'),

                                Forms\Components\TextInput::make('location')
                                    ->label('Localizacion')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Edificio, Piso, Aula'),

                                Select::make('product_ids')
                                    ->label('Productos asociados')
                                    ->multiple()
                                    ->relationship(
                                        name: 'products',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query, Model $record) => $query
                                            ->where('laboratory_id', $record->id)
                                            ->orWhereNull('laboratory_id')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Seleccione los productos que estarán disponibles en este laboratorio'),

                            ])
                            ->columns(2),
                    ])
                    ->compact(),

                Forms\Components\Section::make('Encargado')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Laboratorios')
                            ->options(User::role('LABORATORISTA')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Selecione un encargado')
                            ->helperText('Asigne una persona encargada'),
                    ])
                    ->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Laboratorio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Laboratory $record) => $record->location),

                Tables\Columns\TextColumn::make('capacity')
                    ->badge()
                    ->label('Capacidad')
                    ->formatStateUsing(fn ($state): string => "{$state} people")
                    ->color(fn ($state): string => match (true) {
                        $state > 30 => 'success',
                        $state > 15 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Encargado')
                    ->getStateUsing(fn (Laboratory $record): string => trim($record->user->name.' '.$record->user->last_name))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->tooltip('Edit laboratory'),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Delete laboratory')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Laboratory deleted')
                            ->body('The laboratory was successfully deleted.'),
                    ),
            ])

            ->emptyStateHeading('No laboratories yet')
            ->emptyStateDescription('Create your first laboratory by clicking the button above')
            ->emptyStateIcon('heroicon-o-beaker')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create laboratory')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('name', 'asc')
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaboratories::route('/'),
            'create' => Pages\CreateLaboratory::route('/create'),
            'edit' => Pages\EditLaboratory::route('/{record}/edit'),
        ];
    }
}
