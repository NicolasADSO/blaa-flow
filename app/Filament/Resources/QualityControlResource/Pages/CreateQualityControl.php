<?php

namespace App\Filament\Resources\QualityControlResource\Pages;

use App\Filament\Resources\QualityControlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQualityControl extends CreateRecord
{
    protected static string $resource = QualityControlResource::class;

    /**
     * Después de crear el control de calidad,
     * si fue aprobado, avanzamos el proceso.
     */
    protected function afterCreate(): void
    {
        $qc = $this->record; // registro recién creado
        $process = $qc->controlProcess;

        if ($process && $qc->approved) {
            $process->avanzarAFaseSiguiente();
        }
    }
}
