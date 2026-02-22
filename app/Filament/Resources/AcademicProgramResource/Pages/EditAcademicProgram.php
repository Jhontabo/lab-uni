<?php

namespace App\Filament\Resources\AcademicProgramResource\Pages;

use App\Filament\Resources\AcademicProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademicProgram extends EditRecord
{
    protected static string $resource = AcademicProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
