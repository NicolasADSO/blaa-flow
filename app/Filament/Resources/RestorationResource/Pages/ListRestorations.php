<?php

namespace App\Filament\Resources\RestorationResource\Pages;

use App\Filament\Resources\RestorationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRestorations extends ListRecords
{
    protected static string $resource = RestorationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
