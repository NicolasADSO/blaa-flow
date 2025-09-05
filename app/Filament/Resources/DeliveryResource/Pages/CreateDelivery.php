<?php

namespace App\Filament\Resources\DeliveryResource\Pages;

use App\Filament\Resources\DeliveryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    /**
     * Después de crear la entrega,
     * marcamos el proceso como Finalizado.
     */
    protected function afterCreate(): void
    {
        $delivery = $this->record; // registro recién creado
        $process = $delivery->controlProcess;

        if ($process) {
            $process->update([
                'status' => 'Finalizado',
                'end_date' => now(),
            ]);
        }
    }
}
