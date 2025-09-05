<?php

namespace App\Filament\Resources\ControlProcessResource\Pages;

use App\Filament\Resources\ControlProcessResource;
use App\Models\Phase;
use Filament\Resources\Pages\CreateRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;

class CreateControlProcess extends CreateRecord
{
    protected static string $resource = ControlProcessResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Configuración inicial del proceso')
                ->schema([
                    TextEntry::make('phase.name')
                        ->label('Fase inicial')
                        ->default('Recepción'),
                    TextEntry::make('status')
                        ->label('Estado inicial')
                        ->default('Pendiente'),
                    TextEntry::make('start_date')
                        ->label('Fecha de inicio prevista')
                        ->default(now()->format('d/m/Y H:i')),
                ])
                ->columns(3),
        ]);
    }

    /** Forzamos valores iniciales seguros antes de crear */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'Pendiente';

        // Fase inicial: Recepción si existe, si no, la primera por orden
        $data['phase_id'] = $data['phase_id']
            ?? Phase::where('name', 'Recepción')->value('id')
            ?? Phase::orderBy('order')->value('id');

        // Fecha de inicio si no vino del form
        $data['start_date'] = $data['start_date'] ?? now();

        return $data;
    }

    /** Toast automático de creación */
    protected function getCreatedNotification(): ?Notification
    {
        $fase = $this->record->phase->name ?? 'Recepción';

        return Notification::make()
            ->title('Proceso creado correctamente')
            ->body("El proceso quedó en la fase inicial: {$fase}.")
            ->success();
    }

    /** Redirigir después de crear */
    protected function getRedirectUrl(): string
    {
        // Si prefieres ir al detalle, cambia a: return $this->getResource()::getUrl('view', ['record' => $this->record]);
        return $this->getResource()::getUrl('index');
    }
}
