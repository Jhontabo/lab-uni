<?php

namespace App\Filament\Resources\ReservationHistorysResource\Pages;

use App\Filament\Resources\ReservationHistorysResource;
use Filament\Resources\Pages\ListRecords;

class ListReservationHistories extends ListRecords
{
    protected static string $resource = ReservationHistorysResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
