<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicProgramResource\Pages;
use App\Models\AcademicProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicProgramResource extends Resource
{
    protected static ?string $model = AcademicProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Programas Académicos';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Programa Académico';

    protected static ?string $pluralModelLabel = 'Programas Académicos';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Programa')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Programa')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo: Ingeniería de Sistemas'),

                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ejemplo: IS'),

                                Forms\Components\TextInput::make('faculty')
                                    ->label('Facultad')
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo: Ingeniería'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('faculty')
                    ->label('Facultad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Activo')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->emptyStateHeading('No hay programas académicos')
            ->emptyStateDescription('Crea el primer programa académico')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicPrograms::route('/'),
            'create' => Pages\CreateAcademicProgram::route('/create'),
            'edit' => Pages\EditAcademicProgram::route('/{record}/edit'),
        ];
    }
}
