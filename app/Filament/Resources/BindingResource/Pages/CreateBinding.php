<?php

namespace App\Filament\Resources\BindingResource\Pages;

use App\Filament\Resources\BindingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBinding extends CreateRecord
{
    protected static string $resource = BindingResource::class;

    /**
     * Después de crear la encuadernación,
     * avanzamos el proceso a la siguiente fase.
     */
    protected function afterCreate(): void
    {
        $binding = $this->record; // registro recién creado
        $process = $binding->controlProcess;

        if ($process) {
            $process->avanzarAFaseSiguiente();
        }
    }
}
