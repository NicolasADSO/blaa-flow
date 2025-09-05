<?php

namespace App\Filament\Resources\DigitalizationResource\Pages;

use App\Filament\Resources\DigitalizationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDigitalization extends CreateRecord
{
    protected static string $resource = DigitalizationResource::class;

    /**
     * Después de crear la digitalización,
     * avanzamos el proceso a la siguiente fase.
     */
    protected function afterCreate(): void
    {
        $digitalization = $this->record; // registro recién creado
        $process = $digitalization->controlProcess;

        if ($process) {
            $process->avanzarAFaseSiguiente();
        }
    }
}
