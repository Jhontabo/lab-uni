<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    // protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Mis préstamos';

    protected static ?string $navigationGroup = 'Prestamos';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Préstamo';

    protected static ?string $pluralModelLabel = 'Mis préstamos';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product'])
            ->where('user_id', Auth::id());
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user &&
            ! $user->hasRole('COORDINADOR');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Imagen')
                    ->size(50),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'returned' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'returned' => 'Devuelto',
                        default => ucfirst($state),
                    }),

                TextColumn::make('requested_at')
                    ->label('Fecha petición')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Fecha aprobado')
                    ->dateTime('d M Y H:i')
                    ->placeholder('No aprobado')
                    ->sortable(),

                TextColumn::make('estimated_return_at')
                    ->label('Fecha devolución')
                    ->dateTime('d M Y')
                    ->placeholder('No asignado'),

                TextColumn::make('actual_return_at')
                    ->label('Devuelto')
                    ->dateTime('d M Y H:i')
                    ->placeholder('No devuelto'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'returned' => 'Devuelto',
                    ])
                    ->label('Estado del préstamo'),
            ])
            ->emptyStateHeading('Aún no tienes préstamos registrados');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
        ];
    }
}
