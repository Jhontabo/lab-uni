<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PendingLoansWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableTable(): Table
    {
        return $this->table
            ->query($this->getQuery())
            ->columns([
                ImageColumn::make('product.image')
                    ->label('')
                    ->size(40)
                    ->circular(),

                TextColumn::make('product.name')
                    ->label('Equipo')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->user->name.' '.$record->user->last_name, 'below'),

                TextColumn::make('requested_at')
                    ->label('Solicitado')
                    ->date('d/m/Y H:i')
                    ->icon('heroicon-o-clock')
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Préstamo')
                    ->action(function (Loan $record) {
                        DB::transaction(function () use ($record) {
                            $product = $record->product;
                            if ($product && $product->available_quantity >= 1) {
                                $record->update([
                                    'status' => 'approved',
                                    'approved_at' => now(),
                                    'estimated_return_at' => now()->addWeek(),
                                ]);
                                $product->decrement('available_quantity');
                            }
                        });
                    })
                    ->visible(fn (Loan $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->size('sm')
                    ->requiresConfirmation()
                    ->action(function (Loan $record) {
                        $record->update(['status' => 'rejected']);
                    })
                    ->visible(fn (Loan $record) => $record->status === 'pending'),
            ])
            ->paginated(false)
            ->defaultSort('requested_at', 'asc');
    }

    protected function getQuery(): Builder
    {
        return Loan::where('status', 'pending')
            ->with(['product', 'user'])
            ->orderBy('requested_at', 'asc');
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user && $user->hasRole('LABORATORISTA');
    }
}
