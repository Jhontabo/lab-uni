<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Pages\Page;

class ViewCalendarReadOnly extends Page
{
    protected static string $resource = BookingResource::class;

    protected static string $view = 'filament.pages.booking-calendar-readonly';

    protected static ?string $title = 'Calendario';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $slug = 'calendario';

    public static function canView(): bool
    {
        return true;
    }

    public static function navigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('Calendario')
                ->url(static::getUrl())
                ->icon('heroicon-o-calendar')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteName())),
        ];
    }
}
