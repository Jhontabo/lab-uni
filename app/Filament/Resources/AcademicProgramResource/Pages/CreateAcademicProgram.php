<?php

namespace App\Filament\Resources\AcademicProgramResource\Pages;

use App\Filament\Resources\AcademicProgramResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAcademicProgram extends CreateRecord
{
    protected static string $resource = AcademicProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
