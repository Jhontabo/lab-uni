<?php

namespace App\Filament\Pages;

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
        $user = auth()->user();

        return $user && ($user->hasRole('ADMIN') || $user->hasRole('COORDINADOR') || $user->hasRole('LABORATORISTA'));
    }
}
