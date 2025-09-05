<?php

namespace App\Filament\Resources\CatalogingResource\Pages;

use App\Filament\Resources\CatalogingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCataloging extends EditRecord
{
    protected static string $resource = CatalogingResource::class;

    /** Flag para detectar transición a Finalizado */
    protected bool $advanceAfterSave = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Solo avanzamos si ANTES no estaba finalizado y AHORA sí
        $was = $this->getRecord()->getOriginal('status');
        $now = $data['status'] ?? $was;
        $this->advanceAfterSave = ($was !== 'Finalizado' && $now === 'Finalizado');

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->advanceAfterSave) {
            $cataloging = $this->record;
            $process    = $cataloging->process()->with('phase')->first();

            // Avanza el proceso solo si está en "Catalogación"
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
