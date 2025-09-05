<?php

namespace App\Filament\Resources\ControlProcessPhaseLogResource\Pages;

use App\Filament\Resources\ControlProcessPhaseLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControlProcessPhaseLogs extends ListRecords
{
    protected static string $resource = ControlProcessPhaseLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
