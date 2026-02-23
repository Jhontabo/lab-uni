<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes del Sistema';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.reports';

    protected static function canView(): bool
    {
        return auth()->user() && (
            auth()->user()->hasRole('ADMIN') ||
            auth()->user()->hasRole('COORDINADOR') ||
            auth()->user()->hasRole('LABORATORISTA')
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(route('reports.dashboard.download')),

            Action::make('downloadExcel')
                ->label('Descargar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(route('reports.excel.download')),
        ];
    }
}
