<?php

namespace App\Filament\Resources\RestorationResource\Pages;

use App\Filament\Resources\RestorationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRestoration extends EditRecord
{
    protected static string $resource = RestorationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
