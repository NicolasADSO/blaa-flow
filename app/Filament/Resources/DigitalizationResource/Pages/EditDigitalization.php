<?php

namespace App\Filament\Resources\DigitalizationResource\Pages;

use App\Filament\Resources\DigitalizationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalization extends EditRecord
{
    protected static string $resource = DigitalizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
