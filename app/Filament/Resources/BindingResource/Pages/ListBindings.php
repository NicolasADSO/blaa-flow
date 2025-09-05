<?php

namespace App\Filament\Resources\BindingResource\Pages;

use App\Filament\Resources\BindingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBindings extends ListRecords
{
    protected static string $resource = BindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
