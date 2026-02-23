<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\Page;

class ViewCalendarReadOnly extends Page
{
    protected static string $resource = BookingResource::class;

    protected static string $view = 'filament.pages.booking-calendar-readonly';

    protected static ?string $title = 'Calendario';

    protected static ?string $slug = 'calendario';

    protected static bool $shouldRegisterNavigation = false;

    public static function canView(): bool
    {
        return true;
    }
}
