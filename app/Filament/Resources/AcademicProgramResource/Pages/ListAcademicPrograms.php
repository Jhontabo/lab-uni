<?php

namespace App\Filament\Resources\AcademicProgramResource\Pages;

use App\Filament\Resources\AcademicProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademicPrograms extends ListRecords
{
    protected static string $resource = AcademicProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
