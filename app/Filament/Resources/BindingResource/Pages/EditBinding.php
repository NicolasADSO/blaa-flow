<?php

namespace App\Filament\Resources\BindingResource\Pages;

use App\Filament\Resources\BindingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBinding extends EditRecord
{
    protected static string $resource = BindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
