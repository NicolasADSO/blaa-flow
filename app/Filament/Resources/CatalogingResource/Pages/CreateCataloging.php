<?php

namespace App\Filament\Resources\CatalogingResource\Pages;

use App\Filament\Resources\CatalogingResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateCataloging extends CreateRecord
{
    protected static string $resource = CatalogingResource::class;

    protected function afterCreate(): void
    {
        $cataloging = $this->record;

        // Si se crea ya en "Finalizado", avanzamos el proceso una fase.
        if ($cataloging->status === 'Finalizado') {
            $process = $cataloging->process()->with('phase')->first();

            if ($process && ($process->phase?->name === 'Catalogación')) {
                $process->avanzarAFaseSiguiente(auth()->id());
                $process->refresh();

                Notification::make()
                    ->title('Proceso avanzado')
                    ->body(
                        'El proceso <b>' . e($process->order_number) .
                        '</b> pasó a la fase: <b>' . e($process->phase?->name ?? '—') . '</b>.'
                    )
                    ->success()
                    ->send();
            }
        }

        // ✅ Mantenerse en Catalogación (lista) para evitar 403 por falta de permisos en otras fases.
        $this->redirect(CatalogingResource::getUrl('index'));
    }
}
