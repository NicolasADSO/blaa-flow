<?php

namespace App\Filament\Resources\RestorationResource\Pages;

use App\Filament\Resources\RestorationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestoration extends CreateRecord
{
    protected static string $resource = RestorationResource::class;

    /**
     * Después de crear la restauración,
     * avanzamos el proceso a la siguiente fase.
     */
    protected function afterCreate(): void
    {
        $restoration = $this->record; // el registro recién creado
        $process = $restoration->controlProcess;

        if ($process) {
            $process->avanzarAFaseSiguiente();
        }
    }
}
