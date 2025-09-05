<?php

namespace App\Filament\Resources\ControlProcessPhaseLogResource\Pages;

use App\Filament\Resources\ControlProcessPhaseLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlProcessPhaseLog extends EditRecord
{
    protected static string $resource = ControlProcessPhaseLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
