<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;


    protected static ?string $navigationGroup = 'Administracion';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $modelLabel = 'Rol';
    protected static ?string $pluralModelLabel = 'Roles';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informacion del Rol')
                ->description('Define los detalles basicos del rol')
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del rol')
                        ->placeholder('Example: ADMIN, USER, GUEST')
                        ->autocapitalize('words')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->afterStateUpdated(fn($state, $set) => $set('name', strtoupper($state)))
                        ->columnSpanFull(),
                ]),

            Section::make('Asignacion de permisos')
                ->description('Selecione los permisos')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    Select::make('permissions')
                        ->label('Permisos')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->maxItems(50)
                        ->helperText('Busque y selecione los permisos.')
                        ->loadingMessage('Cargando permisos...')
                        ->noSearchResultsMessage('No se encontro permisos.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre del rol')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->description(fn(Role $record) => $record->permissions->count() . ' permisos asignados'),

                TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 5 => 'info',
                        $state <= 10 => 'primary',
                        default => 'success',
                    }),

                TextColumn::make('created_at')
                    ->label('creacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('actualizacion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No se encontro permisos')
            ->emptyStateDescription('Crea tu primer rol haciendo clic en el botón de arriba..')
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear rol')
                    ->icon('heroicon-o-plus'),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
