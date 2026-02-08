<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoanManagementResource\Pages;
use App\Models\Loan;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanManagementResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Gestión de Préstamos';

    protected static ?string $navigationGroup = 'Prestamos';

    protected static ?string $modelLabel = 'Prestamo';

    protected static ?string $pluralModelLabel = 'Prestamos';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('ADMIN') || $user->hasRole('LABORATORISTA'));
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', Auth::id())->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product:id,name,image,available_quantity', 'user:id,name,last_name,email'])
            ->select(['id', 'product_id', 'user_id', 'status', 'requested_at', 'approved_at', 'estimated_return_at', 'actual_return_at'])
            ->whereIn('status', ['pending', 'approved', 'returned'])
            ->whereNotNull('user_id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Imagen')
                    ->size(51)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraImgAttributes(['class' => 'rounded-lg']),

                TextColumn::make('product.name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => "ID: {$record->product_id}", position: 'above'),

                TextColumn::make('product.available_quantity')
                    ->label('Disponibles')
                    ->sortable()
                    ->color(fn ($state) => $state > 3 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->icon(fn ($state) => $state > 3 ? 'heroicon-o-check-circle' : ($state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle')),

                TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->formatStateUsing(fn ($state, $record) => "{$record->user->name} {$record->user->last_name}")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'approved' => 'heroicon-o-check',
                        'rejected' => 'heroicon-o-x-circle',
                        'returned' => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                TextColumn::make('requested_at')
                    ->label('Solicitud')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('Aprobación')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-badge' : 'heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('estimated_return_at')
                    ->label('Devolución Estimada')
                    ->dateTime('d M Y')
                    ->color(
                        fn ($record) => $record->status === 'approved' && $record->estimated_return_at < now()
                          ? 'danger'
                          : ($record->estimated_return_at ? 'info' : 'gray')
                    )
                    ->icon(
                        fn ($record) => $record->status === 'approved' && $record->estimated_return_at < now()
                          ? 'heroicon-o-exclamation-triangle'
                          : 'heroicon-o-calendar'
                    )
                    ->sortable()
                    ->placeholder('No asignada')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('actual_return_at')
                    ->label('Devuelto')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-archive-box' : 'heroicon-o-truck')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Aprobar Préstamo')
                    ->modalDescription('Confirma la aprobación de este préstamo.')
                    ->form([
                        Forms\Components\DatePicker::make('estimated_return_at')
                            ->label('Fecha estimada de devolución')
                            ->required()
                            ->minDate(now()->addDay())
                            ->default(now()->addWeek())
                            ->displayFormat('d M Y')
                            ->disabled(),
                    ])
                    ->action(function (Loan $record) {
                        DB::transaction(function () use ($record) {
                            $product = $record->product;

                            if ($product->available_quantity < 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error en aprobación')
                                    ->body('No hay unidades disponibles para este producto.')
                                    ->send();

                                return;
                            }

                            $estimatedReturnDate = now()->addWeek();

                            $record->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                                'estimated_return_at' => $estimatedReturnDate,
                            ]);

                            $product->decrement('available_quantity');
                            $product->update(['available_for_loan' => $product->available_quantity >= 1]);

                            Notification::make()
                                ->success()
                                ->title('Préstamo Aprobado')
                                ->body('Fecha límite: '.$estimatedReturnDate->format('d/m/Y'))
                                ->send();

                            Notification::make()
                                ->title('¡Préstamo Aprobado! 🎉')
                                ->body("Tu solicitud para el equipo {$product->name} ha sido aprobada. Fecha límite de devolución: ".$estimatedReturnDate->format('d/m/Y'))
                                ->success()
                                ->icon('heroicon-o-check-circle')
                                ->sendToDatabase($record->user);
                        });
                    })
                    ->visible(fn (Loan $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Rechazar Préstamo')
                    ->requiresConfirmation()
                    ->action(function (Loan $record) {
                        $record->update(['status' => 'rejected', 'estimated_return_at' => null]);

                        Notification::make()
                            ->danger()
                            ->title('Préstamo Rechazado')
                            ->send();

                        Notification::make()
                            ->title('Préstamo Rechazado ❌')
                            ->body("Tu solicitud para el equipo {$record->product->name} ha sido rechazada.")
                            ->danger()
                            ->icon('heroicon-o-x-circle')
                            ->sendToDatabase($record->user);
                    })
                    ->visible(fn (Loan $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('return')
                    ->label('Marcar como Devuelto')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('info')
                    ->modalHeading('Registrar Devolución')
                    ->requiresConfirmation()
                    ->action(function (Loan $record) {
                        DB::transaction(function () use ($record) {
                            $actualReturnDate = now();
                            $product = $record->product;

                            $record->update([
                                'status' => 'returned',
                                'actual_return_at' => $actualReturnDate,
                            ]);

                            $product->increment('available_quantity');
                            $product->update(['available_for_loan' => true]);

                            Notification::make()
                                ->success()
                                ->title('Equipo Devuelto')
                                ->send();

                            Notification::make()
                                ->title('¡Equipo Devuelto! ✅')
                                ->body("Gracias por devolver el equipo {$product->name} a tiempo.")
                                ->success()
                                ->icon('heroicon-o-archive-box-arrow-down')
                                ->sendToDatabase($record->user);
                        });
                    })
                    ->visible(fn (Loan $record) => $record->status === 'approved'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No hay préstamos registrados')
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultSort('requested_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanManagements::route('/'),
        ];
    }
}
