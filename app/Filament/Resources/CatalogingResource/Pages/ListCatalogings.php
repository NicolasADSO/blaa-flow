<?php

namespace App\Filament\Resources\CatalogingResource\Pages;

use App\Filament\Resources\CatalogingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCatalogings extends ListRecords
{
    protected static string $resource = CatalogingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
